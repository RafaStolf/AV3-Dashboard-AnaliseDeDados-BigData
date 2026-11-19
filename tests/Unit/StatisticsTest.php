<?php

namespace Tests\Unit;

use App\Services\Statistics;
use PHPUnit\Framework\TestCase;

class StatisticsTest extends TestCase
{
    public function test_quantiles_interpolate_and_preserve_missing_data(): void
    {
        $this->assertSame(2.5, Statistics::quantile([1, null, 2, 3, 4], .5));
        $this->assertSame(1.75, Statistics::quantile([1, 2, 3, 4], .25));
        $this->assertNull(Statistics::quantile([null], .5));
        $this->assertSame(0.0, Statistics::quantile([0], .5));
    }

    public function test_pearson_uses_paired_observations_and_handles_constants(): void
    {
        $this->assertEqualsWithDelta(1, Statistics::pearson([[1, 2], [2, 4], [3, 6], [null, 10]])['r'], 1e-10);
        $this->assertSame(3, Statistics::pearson([[1, 2], [2, 4], [3, 6], [null, 10]])['n']);
        $this->assertNull(Statistics::pearson([[1, 2], [1, 3]])['r']);
    }
}
