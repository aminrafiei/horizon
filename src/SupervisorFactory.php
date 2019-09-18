<?php

namespace Aminrafiei\Horizon;

class SupervisorFactory
{
    /**
     * Create a new supervisor instance.
     *
     * @param  \Aminrafiei\Horizon\SupervisorOptions  $options
     * @return \Aminrafiei\Horizon\Supervisor
     */
    public function make(SupervisorOptions $options)
    {
        return new Supervisor($options);
    }
}
