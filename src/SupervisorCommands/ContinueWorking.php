<?php

namespace Aminrafiei\Horizon\SupervisorCommands;

use Aminrafiei\Horizon\Contracts\Pausable;

class ContinueWorking
{
    /**
     * Process the command.
     *
     * @param  \Aminrafiei\Horizon\Contracts\Pausable  $pausable
     * @return void
     */
    public function process(Pausable $pausable)
    {
        $pausable->continue();
    }
}
