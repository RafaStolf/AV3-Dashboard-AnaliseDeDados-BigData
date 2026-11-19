<?php

namespace App\Services;

final class Statistics
{
    public static function quantile(array $values, float $p): ?float
    {
        $values = array_values(array_filter($values, fn ($v) => $v !== null && is_numeric($v)));
        if (! count($values)) {
            return null;
        }
        sort($values, SORT_NUMERIC);
        $index = (count($values) - 1) * $p;
        $lo = (int) floor($index);
        $hi = (int) ceil($index);

        return (float) ($values[$lo] + ($values[$hi] - $values[$lo]) * ($index - $lo));
    }

    public static function pearson(array $pairs): array
    {
        $pairs = array_values(array_filter($pairs, fn ($p) => $p[0] !== null && $p[1] !== null));
        $n = count($pairs);
        if ($n < 2) {
            return ['r' => null, 'n' => $n];
        }
        $mx = array_sum(array_column($pairs, 0)) / $n;
        $my = array_sum(array_column($pairs, 1)) / $n;
        $xy = $xx = $yy = 0;
        foreach ($pairs as [$x, $y]) {
            $xy += ($x - $mx) * ($y - $my);
            $xx += ($x - $mx) ** 2;
            $yy += ($y - $my) ** 2;
        }

        return ['r' => $xx * $yy > 0 ? max(-1, min(1, $xy / sqrt($xx * $yy))) : null, 'n' => $n];
    }
}
