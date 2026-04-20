<?php

declare(strict_types = 1);

namespace Centrex\Crm\Services;

use Carbon\Carbon;

class ClvCalculator
{
    /**
     * Calculate CLV from an array of transactions.
     *
     * Each transaction: ['date' => Carbon|string, 'amount' => float]
     */
    public function calculate(array $transactions, int $horizonMonths = 12, float $discountRate = 0.1): array
    {
        if ($transactions === []) {
            return $this->emptyResult($horizonMonths);
        }

        $normalized = array_map(function (array $t): array {
            return [
                'date'   => $t['date'] instanceof Carbon ? $t['date'] : Carbon::parse($t['date']),
                'amount' => (float) $t['amount'],
            ];
        }, $transactions);

        usort($normalized, static fn (array $a, array $b): int => $a['date']->lt($b['date']) ? -1 : 1);

        $first       = $normalized[0]['date'];
        $last        = $normalized[count($normalized) - 1]['date'];
        $now         = Carbon::now();
        $n           = count($normalized);
        $totalRevenue = (float) array_sum(array_column($normalized, 'amount'));
        $avgValue    = $totalRevenue / $n;

        $frequency    = max(0, $n - 1);
        $recencyDays  = (float) $first->diffInDays($last);
        $ageDays      = max(1.0, (float) $first->diffInDays($now));
        $daysSinceLast = (float) $last->diffInDays($now);

        $avgInterval = $frequency > 0 ? $ageDays / $frequency : $ageDays;
        $pAlive = (float) exp(-$daysSinceLast / max(1.0, $avgInterval * 2.0));
        $pAlive = min(1.0, max(0.01, $pAlive));

        $monthlyRevenue = $totalRevenue / ($ageDays / 30.44);
        $clv = ($monthlyRevenue * $pAlive * $horizonMonths) / (1.0 + $discountRate);

        $expectedTransactions = $pAlive * max(1, $frequency) / ($ageDays / 30.44) * $horizonMonths;

        return [
            'clv_value'              => round($clv, 2),
            'expected_monthly_value' => round($monthlyRevenue * $pAlive, 2),
            'avg_deal_value'         => round($avgValue, 2),
            'total_revenue'          => round($totalRevenue, 2),
            'frequency'              => $frequency,
            'recency_days'           => round($recencyDays, 2),
            'age_days'               => round($ageDays, 2),
            'p_alive'                => round($pAlive, 4),
            'expected_transactions'  => round($expectedTransactions, 2),
            'horizon_months'         => $horizonMonths,
        ];
    }

    private function emptyResult(int $horizonMonths): array
    {
        return [
            'clv_value'              => 0.0,
            'expected_monthly_value' => 0.0,
            'avg_deal_value'         => 0.0,
            'total_revenue'          => 0.0,
            'frequency'              => 0,
            'recency_days'           => 0.0,
            'age_days'               => 0.0,
            'p_alive'                => 0.0,
            'expected_transactions'  => 0.0,
            'horizon_months'         => $horizonMonths,
        ];
    }
}
