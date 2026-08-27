<?php

namespace App\Http\Controllers;

use App\Models\CavData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CavController extends Controller
{
    public function index(Request $request)
    {
        $query = CavData::query();

        if (Auth::check()) {
            $query->where('user_id', Auth::id());
        }

        $data = $query
            ->orderBy('data_registro', 'asc')
            ->get([
                'id',
                'user_id',
                'data_registro',
                'cota',
                'area',
                'volume',
            ]);

        return response()->json([
            'data' => $data,
            'count' => $data->count(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'data_registro' => ['required', 'date'],
            'cota' => ['required', 'numeric'],
            'area' => ['required', 'numeric'],
            'volume' => ['required', 'numeric'],
        ]);

        $record = CavData::create([
            'user_id' => Auth::id(),
            'data_registro' => $validated['data_registro'],
            'cota' => $validated['cota'],
            'area' => $validated['area'],
            'volume' => $validated['volume'],
        ]);

        return response()->json([
            'message' => 'Registro salvo com sucesso.',
            'data' => $record,
        ], 201);
    }

    public function forecast(Request $request)
    {
        $query = CavData::query();

        if (Auth::check()) {
            $query->where('user_id', Auth::id());
        }

        $records = $query
            ->orderBy('data_registro', 'asc')
            ->get(['data_registro', 'volume']);

        if ($records->isEmpty()) {
            return response()->json([
                'message' => 'Nenhum dado registrado ainda.',
                'series' => [],
                'forecast' => [],
            ]);
        }

        $series = $records->map(function ($record) {
            return [
                'label' => $record->data_registro->format('Y-m'),
                'value' => (float) $record->volume,
            ];
        })->values()->all();

        $values = array_map(fn ($point) => $point['value'], $series);
        $count = count($values);

        $forecast = [];
        if ($count >= 2) {
            $xMean = ($count - 1) / 2;
            $yMean = array_sum($values) / $count;
            $numerator = 0;
            $denominator = 0;

            foreach ($values as $index => $value) {
                $diffX = $index - $xMean;
                $diffY = $value - $yMean;
                $numerator += $diffX * $diffY;
                $denominator += $diffX * $diffX;
            }

            $slope = $denominator === 0 ? 0 : $numerator / $denominator;
            $intercept = $yMean - ($slope * $xMean);

            for ($step = 1; $step <= 6; $step++) {
                $nextIndex = $count + $step - 1;
                $projected = $intercept + ($slope * $nextIndex);

                $forecast[] = [
                    'label' => $this->nextPeriodLabel($records->last()->data_registro, $step),
                    'value' => round(max(0, $projected), 4),
                ];
            }
        }

        return response()->json([
            'series' => $series,
            'forecast' => $forecast,
        ]);
    }

    private function nextPeriodLabel($lastDate, int $step): string
    {
        $date = $lastDate->copy()->addMonthsNoOverflow($step);

        return $date->format('Y-m');
    }
}
