<?php

namespace Aminrafiei\Horizon\Tests\Feature\Jobs;

class BasicJob
{
    public function handle()
    {
        //
    }

    public function tags()
    {
        return ['first', 'second'];
    }
}
