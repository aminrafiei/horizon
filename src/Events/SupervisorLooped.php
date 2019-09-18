<?php

namespace Aminrafiei\Horizon\Events;

use Aminrafiei\Horizon\Supervisor;

class SupervisorLooped
{
    /**
     * The supervisor instance.
     *
     * @var \Aminrafiei\Horizon\Supervisor
     */
    public $supervisor;

    /**
     * Create a new event instance.
     *
     * @param  \Aminrafiei\Horizon\Supervisor  $supervisor
     * @return void
     */
    public function __construct(Supervisor $supervisor)
    {
        $this->supervisor = $supervisor;
    }
}
