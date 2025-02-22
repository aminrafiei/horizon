<?php

namespace Aminrafiei\Horizon\Tests\Unit;

use Aminrafiei\Horizon\Stopwatch;
use Aminrafiei\Horizon\Tests\UnitTest;

class StopwatchTest extends UnitTest
{
    public function test_time_between_checks_can_be_measured()
    {
        $stopwatch = new Stopwatch;
        $stopwatch->start('foo');
        usleep(10 * 1000);
        $difference = $stopwatch->check('foo');

        // Make sure the millisecond difference is within a normal range of variance...
        $this->assertGreaterThan(0, $difference);
    }
}
