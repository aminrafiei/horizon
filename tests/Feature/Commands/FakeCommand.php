<?php

namespace Aminrafiei\Horizon\Tests\Feature\Commands;

use Aminrafiei\Horizon\Supervisor;

class FakeCommand
{
    public $processCount = 0;
    public $supervisor;
    public $options;

    public function process(Supervisor $supervisor, array $options)
    {
        $this->processCount++;
        $this->supervisor = $supervisor;
        $this->options = $options;
    }
}
