<?php

namespace Aminrafiei\Horizon\Tests\Unit;

use Mockery;
use Aminrafiei\Horizon\Supervisor;
use Aminrafiei\Horizon\Tests\UnitTest;
use Aminrafiei\Horizon\SupervisorCommands\Scale;

class ScaleCommandTest extends UnitTest
{
    public function test_scale_command_tells_supervisor_to_scale()
    {
        $supervisor = Mockery::mock(Supervisor::class);
        $supervisor->shouldReceive('scale')->once()->with(3);
        $scale = new Scale;
        $scale->process($supervisor, ['scale' => 3]);
    }
}
