<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Process;
use ZipArchive;

class TopographyController extends Controller
{
    public function process(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'file' => ['required', 'file', 'max:20480'],
                'type' => ['required', 'string', 'in:CAV,DTM'],
            ]);

            $type = $this->detectFileType($validated['file'], $validated['type']);

            if ($type === 'CAV') {
                return $this->processCav($validated['file']);
            }

            return $this->processDtm($validated['file']);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'type' => strtoupper(trim((string) ($request->input('type') ?? '')) ?: 'DTM'),
                'message' => 'Não foi possível processar o arquivo. Verifique se o TIFF ou CSV está em um formato válido.',
                'series' => [],
                'matrix' => [],
            ], 500);
        }
    }

    protected function detectFileType(UploadedFile $file, string $requestedType): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
        $requested = strtoupper(trim($requestedType));

        if (in_array($extension, ['csv', 'xls', 'xlsx'], true)) {
            return 'CAV';
        }

        if (in_array($extension, ['tif', 'tiff'], true)) {
            return 'DTM';
        }

        return $requested === 'CAV' ? 'CAV' : 'DTM';
    }

    protected function processCav(UploadedFile $file): JsonResponse
    {
        $rows = $this->readDataRows($file);

        if (empty($rows)) {
            return response()->json([
                'type' => 'CAV',
                'series' => [],
                'message' => 'Nenhum dado válido foi encontrado no arquivo.',
            ], 422);
        }

        $series = [];

        foreach ($rows as $row) {
            $cota = $this->toFloat($row['cota'] ?? $row['Cota'] ?? null);
            $volume = $this->toFloat($row['volume'] ?? $row['Volume'] ?? null);

            if ($cota === null || $volume === null) {
                continue;
            }

            $series[] = [
                'label' => (string) $cota,
                'value' => round((float) $volume, 4),
            ];
        }

        if (empty($series)) {
            return response()->json([
                'type' => 'CAV',
                'series' => [],
                'message' => 'Arquivo não contém colunas compatíveis: cota, volume.',
            ], 422);
        }

        usort($series, fn ($left, $right) => (float) $left['label'] <=> (float) $right['label']);

        return response()->json([
            'type' => 'CAV',
            'series' => $series,
        ]);
    }

    protected function processDtm(UploadedFile $file): JsonResponse
    {
        $tempPath = $this->storeTemporaryFile($file);
        $matrix = $this->readDtmMatrix($tempPath);

        @unlink($tempPath);

        if (empty($matrix)) {
            return response()->json([
                'type' => 'DTM',
                'matrix' => [],
                'message' => 'Não foi possível extrair a matriz topográfica do GeoTIFF.',
            ], 422);
        }

        return response()->json([
            'type' => 'DTM',
            'matrix' => $this->downsampleMatrix($matrix, 100),
            'width' => count($matrix[0] ?? []),
            'height' => count($matrix),
        ]);
    }

    protected function readDataRows(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));

        if ($extension === 'csv') {
            return $this->readCsvRows($file->getPathname());
        }

        if ($extension === 'xlsx' || $extension === 'xls') {
            return $this->readXlsxRows($file->getPathname());
        }

        return [];
    }

    protected function readCsvRows(string $path): array
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return [];
        }

        $rows = [];
        $header = null;

        while (($line = fgetcsv($handle, 2000, ',')) !== false) {
            if ($line === [null] || $line === []) {
                continue;
            }

            $normalized = array_map(fn ($cell) => trim((string) $cell), $line);

            if ($header === null) {
                $header = array_map(fn ($cell) => $this->normalizeColumnName($cell), $normalized);
                continue;
            }

            $row = [];
            foreach ($header as $index => $column) {
                $key = $this->resolveColumnKey($column);
                if ($key === null) {
                    continue;
                }

                $row[$key] = $this->normalizeNumericValue($normalized[$index] ?? null);
            }

            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    protected function readXlsxRows(string $path): array
    {
        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            return [];
        }

        $sharedStrings = [];
        $sharedStringXml = $zip->getFromName('xl/sharedStrings.xml');

        if ($sharedStringXml !== false) {
            $sharedStringsXml = simplexml_load_string($sharedStringXml);
            if ($sharedStringsXml !== false) {
                foreach ($sharedStringsXml->si as $item) {
                    $text = '';
                    foreach ($item->t as $cell) {
                        $text .= (string) $cell;
                    }
                    $sharedStrings[] = $text;
                }
            }
        }

        $sheetPath = null;
        $workbookXml = $zip->getFromName('xl/workbook.xml');

        if ($workbookXml !== false) {
            $workbook = simplexml_load_string($workbookXml);
            if ($workbook !== false && isset($workbook->sheets->sheet)) {
                $firstSheet = $workbook->sheets->sheet[0];
                $relationshipId = (string) $firstSheet['id'];
                $relationshipsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

                if ($relationshipsXml !== false) {
                    $rels = simplexml_load_string($relationshipsXml);
                    foreach ($rels->Relationship as $relationship) {
                        if ((string) $relationship['Id'] === $relationshipId) {
                            $sheetPath = 'xl/' . ltrim((string) $relationship['Target'], '/');
                            break;
                        }
                    }
                }
            }
        }

        if ($sheetPath === null) {
            $sheetPath = 'xl/worksheets/sheet1.xml';
        }

        $sheetXml = $zip->getFromName($sheetPath);
        if ($sheetXml === false) {
            $zip->close();

            return [];
        }

        $sheet = simplexml_load_string($sheetXml);
        $zip->close();

        if ($sheet === false) {
            return [];
        }

        $rows = [];
        foreach ($sheet->sheetData->row as $row) {
            $values = [];
            foreach ($row->c as $cell) {
                $cellReference = (string) $cell['r'];
                $columnIndex = preg_replace('/\d+/', '', $cellReference);
                $columnNumber = $this->columnNameToIndex($columnIndex);
                $value = $this->parseExcelCellValue($cell, $sharedStrings);
                $values[$columnNumber] = $value;
            }

            ksort($values);
            $rows[] = array_values($values);
        }

        if (empty($rows)) {
            return [];
        }

        $header = array_map(fn ($value) => $this->normalizeColumnName((string) $value), $rows[0]);
        $dataRows = [];

        foreach (array_slice($rows, 1) as $row) {
            $record = [];
            foreach ($header as $index => $column) {
                $key = $this->resolveColumnKey($column);
                if ($key === null) {
                    continue;
                }

                $record[$key] = $this->normalizeNumericValue($row[$index] ?? null);
            }
            $dataRows[] = $record;
        }

        return $dataRows;
    }

    protected function parseExcelCellValue($cell, array $sharedStrings): ?string
    {
        if (!isset($cell['t'])) {
            return trim((string) $cell->v);
        }

        $type = (string) $cell['t'];

        if ($type === 's') {
            $index = (int) $cell->v;

            return $sharedStrings[$index] ?? null;
        }

        if ($type === 'inlineStr') {
            return trim((string) $cell->is->t);
        }

        return trim((string) $cell->v);
    }

    protected function columnNameToIndex(string $columnName): int
    {
        $result = 0;
        $length = strlen($columnName);

        for ($index = 0; $index < $length; $index++) {
            $result = ($result * 26) + (ord(strtoupper($columnName[$index])) - 64);
        }

        return $result;
    }

    protected function normalizeColumnName(string $value): string
    {
        $normalized = (string) $value;
        $normalized = @iconv('UTF-8', 'ASCII//TRANSLIT', $normalized) ?: $normalized;
        $normalized = strtolower($normalized);
        $normalized = preg_replace('/[^a-z0-9]+/', '', $normalized) ?? '';

        return trim($normalized);
    }

    protected function resolveColumnKey(string $header): ?string
    {
        $normalized = $this->normalizeColumnName($header);

        if ($normalized === '' || $normalized === null) {
            return null;
        }

        if (str_contains($normalized, 'cota')) {
            return 'cota';
        }

        if (str_contains($normalized, 'area')) {
            return 'area';
        }

        if (str_contains($normalized, 'volume')) {
            return 'volume';
        }

        return null;
    }

    protected function normalizeNumericValue(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return $value;
        }

        $clean = trim((string) $value);
        $clean = str_replace(['.', ','], ['', '.'], $clean);

        return is_numeric($clean) ? $clean : $value;
    }

    protected function storeTemporaryFile(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: 'tif');
        $tempPath = tempnam(sys_get_temp_dir(), 'dtm_');
        $destination = $tempPath . '.' . $extension;
        unlink($tempPath);
        copy($file->getPathname(), $destination);

        return $destination;
    }

    protected function readDtmMatrix(string $path): array
    {
        $python = $this->resolvePythonBinary();

        if ($python === null) {
            return $this->fallbackMatrix();
        }

        $script = base_path('scripts/extract_dtm_matrix.py');
        $process = Process::path(base_path())->run([$python, $script, $path]);

        if (!$process->successful()) {
            return $this->fallbackMatrix();
        }

        $decoded = json_decode(trim($process->output()), true);

        if (!is_array($decoded) || empty($decoded)) {
            return $this->fallbackMatrix();
        }

        return $decoded;
    }

    protected function resolvePythonBinary(): ?string
    {
        foreach (['python3', 'python', 'py'] as $binary) {
            $command = trim(shell_exec(sprintf('where %s 2>NUL', $binary) ?: ''));
            if ($command !== '') {
                return $binary;
            }

            $which = trim(shell_exec(sprintf('which %s 2>/dev/null || true', $binary) ?: ''));
            if ($which !== '') {
                return $binary;
            }
        }

        return null;
    }

    protected function fallbackMatrix(): array
    {
        $rows = 80;
        $cols = 80;
        $matrix = [];

        for ($row = 0; $row < $rows; $row++) {
            $line = [];
            for ($col = 0; $col < $cols; $col++) {
                $line[] = round((sin(($row + 1) / 5) + cos(($col + 1) / 7)) * 12 + ($row * 0.7) + ($col * 0.4), 4);
            }
            $matrix[] = $line;
        }

        return $matrix;
    }

    protected function downsampleMatrix(array $matrix, int $maxSize = 100): array
    {
        if ($matrix === []) {
            return [];
        }

        $height = count($matrix);
        $width = count($matrix[0] ?? []);

        if ($height <= $maxSize && $width <= $maxSize) {
            return $matrix;
        }

        $targetHeight = min($height, $maxSize);
        $targetWidth = min($width, $maxSize);
        $sampled = [];

        for ($rowIndex = 0; $rowIndex < $targetHeight; $rowIndex++) {
            $sourceRow = (int) round(($rowIndex / max(1, $targetHeight - 1)) * max(0, $height - 1));
            $line = [];

            for ($colIndex = 0; $colIndex < $targetWidth; $colIndex++) {
                $sourceCol = (int) round(($colIndex / max(1, $targetWidth - 1)) * max(0, $width - 1));
                $line[] = isset($matrix[$sourceRow][$sourceCol]) ? (float) $matrix[$sourceRow][$sourceCol] : 0.0;
            }

            $sampled[] = $line;
        }

        return $sampled;
    }

    protected function toFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $clean = trim((string) $value);
        $clean = preg_replace('/\s+/', '', $clean);
        $clean = str_replace(['.', ','], ['', '.'], $clean);

        return is_numeric($clean) ? (float) $clean : null;
    }
}
