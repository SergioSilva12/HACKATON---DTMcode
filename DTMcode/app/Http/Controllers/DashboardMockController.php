<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class DashboardMockController extends BaseController
{
    /**
     * Retorna JSON mockado para o dashboard associado ao e-mail informado.
     * Endpoint exemplo: GET /api/dashboard/mock-data?email=sergiolimasilva12@gmail.com
     */
    public function mockData(Request $request)
    {
        $email = $request->query('email');
        $owner = 'sergiolimasilva12@gmail.com';

        if ($email !== $owner) {
            return response()->json(['error' => 'No mock data for this email.'], 404);
        }

        // Volume histórico (CAV) — exemplo simples
        $volume_history = [];
        $start = new \DateTimeImmutable('2024-01-01');
        for ($i = 0; $i < 18; $i++) {
            $date = $start->modify("+$i months")->format('Y-m-d');
            $level = 100 + 5 * sin($i * 0.6);
            $area = 5000 + 200 * cos($i * 0.4);
            $volume = round($level * $area * 0.001);
            $volume_history[] = [
                'date' => $date,
                'level' => round($level, 2),
                'area' => (int)round($area),
                'volume' => (int)$volume,
            ];
        }

        // Matriz Z 50x50 exemplo (bacia com depressão central)
        $rows = 50; $cols = 50;
        $cx = ($cols - 1) / 2.0; $cy = ($rows - 1) / 2.0;
        $z = [];
        for ($r = 0; $r < $rows; $r++) {
            $row = [];
            for ($c = 0; $c < $cols; $c++) {
                $d = hypot($c - $cx, $r - $cy);
                $h = 40 - 20 * exp(-0.02 * $d * $d) + (mt_rand(-50, 50) / 100.0);
                $row[] = round($h, 2);
            }
            $z[] = $row;
        }

        return response()->json([
            'owner' => $owner,
            'volume_history' => $volume_history,
            'z_matrix' => $z,
        ]);
    }
}
