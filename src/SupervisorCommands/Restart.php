<?php

namespace Aminrafiei\Horizon\SupervisorCommands;

use Aminrafiei\Horizon\Contracts\Restartable;

class Restart
{
    /**
     * Process the command.
     *
     * @param  \Aminrafiei\Horizon\Contracts\Restartable  $restartable
     * @return void
     */
    public function process(Restartable $restartable)
    {
        $restartable->restart();
    }
}
