<?php

namespace Aminrafiei\Horizon\Events;

use Aminrafiei\Horizon\MasterSupervisor;

class MasterSupervisorLooped
{
    /**
     * The master supervisor instance.
     *
     * @var \Aminrafiei\Horizon\MasterSupervisor
     */
    public $master;

    /**
     * Create a new event instance.
     *
     * @param  \Aminrafiei\Horizon\MasterSupervisor  $master
     * @return void
     */
    public function __construct(MasterSupervisor $master)
    {
        $this->master = $master;
    }
}
