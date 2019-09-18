<?php

namespace Aminrafiei\Horizon\Tests\Feature\Fixtures;

use Aminrafiei\Horizon\SupervisorFactory;
use Aminrafiei\Horizon\SupervisorOptions;
use Aminrafiei\Horizon\Tests\Feature\Fakes\SupervisorWithFakeMonitor;

class FakeSupervisorFactory extends SupervisorFactory
{
    public $supervisor;

    public function make(SupervisorOptions $options)
    {
        return $this->supervisor = new SupervisorWithFakeMonitor($options);
    }
}
