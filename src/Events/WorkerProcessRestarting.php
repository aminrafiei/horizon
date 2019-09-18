<?php

namespace Aminrafiei\Horizon\Events;

use Aminrafiei\Horizon\WorkerProcess;

class WorkerProcessRestarting
{
    /**
     * The worker process instance.
     *
     * @var \Aminrafiei\Horizon\WorkerProcess
     */
    public $process;

    /**
     * Create a new event instance.
     *
     * @param  \Aminrafiei\Horizon\WorkerProcess  $process
     * @return void
     */
    public function __construct(WorkerProcess $process)
    {
        $this->process = $process;
    }
}
