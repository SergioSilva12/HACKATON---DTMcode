<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class TopographyProcessingTest extends TestCase
{
    public function test_it_processes_cav_csv_upload_and_returns_chart_data(): void
    {
        $user = User::factory()->create();

        $file = new UploadedFile(
            __DIR__ . '/fixtures/cav_sample.csv',
            'cav_sample.csv',
            'text/csv',
            null,
            true
        );

        $response = $this->actingAs($user)->postJson('/api/topography/process', [
            'file' => $file,
            'type' => 'CAV',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'type',
            'series' => [
                ['label', 'value'],
            ],
        ]);

        $response->assertJsonPath('type', 'CAV');
        $this->assertNotEmpty($response->json('series'));
    }

    public function test_guest_can_process_topography_files_without_authentication(): void
    {
        $file = new UploadedFile(
            __DIR__ . '/fixtures/cav_sample.csv',
            'cav_sample.csv',
            'text/csv',
            null,
            true
        );

        $response = $this->postJson('/api/topography/process', [
            'file' => $file,
            'type' => 'CAV',
        ]);

        $response->assertOk();
        $response->assertJsonPath('type', 'CAV');
    }

    public function test_guest_can_process_dtm_tif_upload_without_authentication(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'dtm_test_');
        file_put_contents($tempFile . '.tif', "II*\x00\x08\x00\x00\x00\x00\x00\x00\x00");

        $file = new UploadedFile(
            $tempFile . '.tif',
            'sample.tif',
            'image/tiff',
            null,
            true
        );

        $response = $this->postJson('/api/topography/process', [
            'file' => $file,
            'type' => 'DTM',
        ]);

        $response->assertOk();
        $response->assertJsonPath('type', 'DTM');

        unlink($tempFile . '.tif');
    }

    public function test_it_processes_cav_csv_with_accented_headers_and_decimal_commas(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'cav_csv_');
        $csv = "Cota (m),Área (km²),Volume (m³)\n";
        $csv .= "220.13,0,0\n";
        $csv .= "221,0.0001,38\n";
        $csv .= "222,0.0005,291\n";
        $csv .= "223,0.0022,1471\n";

        file_put_contents($tempFile, $csv);

        $file = new UploadedFile(
            $tempFile,
            'cav_real.csv',
            'text/csv',
            null,
            true
        );

        $response = $this->postJson('/api/topography/process', [
            'file' => $file,
            'type' => 'CAV',
        ]);

        $response->assertOk();
        $response->assertJsonPath('type', 'CAV');
        $this->assertNotEmpty($response->json('series'));

        unlink($tempFile);
    }

    public function test_csv_file_is_auto_detected_as_cav_even_when_type_is_dtm(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'cav_csv_');
        $csv = "Cota (m),Área (km²),Volume (m³)\n";
        $csv .= "220.13,0,0\n";
        $csv .= "221,0.0001,38\n";
        file_put_contents($tempFile, $csv);

        $file = new UploadedFile(
            $tempFile,
            'trussu.csv',
            'text/csv',
            null,
            true
        );

        $response = $this->postJson('/api/topography/process', [
            'file' => $file,
            'type' => 'DTM',
        ]);

        $response->assertOk();
        $response->assertJsonPath('type', 'CAV');
        $this->assertNotEmpty($response->json('series'));

        unlink($tempFile);
    }

    public function test_it_processes_valid_tif_file_as_dtm(): void
    {
        $tempDir = sys_get_temp_dir();
        $tempFile = $tempDir . DIRECTORY_SEPARATOR . 'dtm_valid_test_' . uniqid() . '.tif';

        $script = sprintf(
            'python -c "import numpy as np, rasterio; from rasterio.transform import from_origin; a = np.arange(25, dtype=%s).reshape(5, 5); dst = rasterio.open(r%s, %s, driver=%s, height=a.shape[0], width=a.shape[1], count=1, dtype=a.dtype, crs=%s, transform=from_origin(0, 5, 1, 1)); dst.write(a, 1); dst.close()"',
            "'float32'",
            "'" . str_replace('\\', '\\\\', $tempFile) . "'",
            "'w'",
            "'GTiff'",
            "'EPSG:4326'"
        );

        exec($script, $output, $code);

        $this->assertSame(0, $code, 'The test TIFF could not be created.');
        $this->assertFileExists($tempFile);

        $file = new UploadedFile(
            $tempFile,
            'valid_dtm.tif',
            'image/tiff',
            null,
            true
        );

        $response = $this->postJson('/api/topography/process', [
            'file' => $file,
            'type' => 'DTM',
        ]);

        $response->assertOk();
        $response->assertJsonPath('type', 'DTM');
        $this->assertNotEmpty($response->json('matrix'));

        unlink($tempFile);
    }
}
