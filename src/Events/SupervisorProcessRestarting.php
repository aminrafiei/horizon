<?php

namespace Aminrafiei\Horizon\Events;

use Aminrafiei\Horizon\SupervisorProcess;

class SupervisorProcessRestarting
{
    /**
     * The supervisor process instance.
     *
     * @var \Aminrafiei\Horizon\SupervisorProcess
     */
    public $process;

    /**
     * Create a new event instance.
     *
     * @param  \Aminrafiei\Horizon\SupervisorProcess  $process
     * @return void
     */
    public function __construct(SupervisorProcess $process)
    {
        $this->process = $process;
    }
}
