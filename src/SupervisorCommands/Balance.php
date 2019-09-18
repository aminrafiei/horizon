<?php

namespace Aminrafiei\Horizon\SupervisorCommands;

use Aminrafiei\Horizon\Supervisor;

class Balance
{
    /**
     * Process the command.
     *
     * @param  \Aminrafiei\Horizon\Supervisor  $supervisor
     * @param  array  $options
     * @return void
     */
    public function process(Supervisor $supervisor, array $options)
    {
        $supervisor->balance($options);
    }
}
