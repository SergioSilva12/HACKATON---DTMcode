<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WaterForecastController extends Controller
{
    public function forecast(Request $request)
    {
        $period = $request->query('period', 'months');
        $series = $request->input('series', [
            ['label' => 'Jan', 'value' => 112],
            ['label' => 'Fev', 'value' => 118],
            ['label' => 'Mar', 'value' => 126],
            ['label' => 'Abr', 'value' => 136],
            ['label' => 'Mai', 'value' => 149],
            ['label' => 'Jun', 'value' => 162],
        ]);

        $historical = array_values(array_map(function ($item) {
            return [
                'label' => $item['label'] ?? 'N/A',
                'value' => (float) ($item['value'] ?? 0),
            ];
        }, $series));

        $values = array_map(fn ($item) => (float) $item['value'], $historical);
        $size = count($values);

        $forecast = [];
        if ($size >= 2) {
            $x = range(0, $size - 1);
            $xMean = array_sum($x) / $size;
            $yMean = array_sum($values) / $size;

            $numerator = 0;
            $denominator = 0;

            foreach ($x as $index => $xi) {
                $yi = $values[$index];
                $numerator += ($xi - $xMean) * ($yi - $yMean);
                $denominator += pow($xi - $xMean, 2);
            }

            $slope = $denominator === 0 ? 0 : $numerator / $denominator;
            $intercept = $yMean - ($slope * $xMean);

            for ($step = 1; $step <= 6; $step++) {
                $nextIndex = $size + $step - 1;
                $predicted = $intercept + ($slope * $nextIndex);

                $forecast[] = [
                    'label' => $this->generateLabel($period, $step, $size),
                    'value' => round(max($predicted, 0), 2),
                ];
            }
        }

        return response()->json([
            'period' => $period,
            'history' => $historical,
            'forecast' => $forecast,
        ]);
    }

    private function generateLabel(string $period, int $step, int $size): string
    {
        if ($period === 'years') {
            return 'Ano ' . ($size + $step);
        }

        $months = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        $index = ($size + $step - 1) % 12;

        return $months[$index] . ' ' . (int) floor(($size + $step - 1) / 12 + 2025);
    }
}
