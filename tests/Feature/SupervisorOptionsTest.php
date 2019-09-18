<?php

namespace Aminrafiei\Horizon\Tests\Feature;

use Aminrafiei\Horizon\SupervisorOptions;
use Aminrafiei\Horizon\Tests\IntegrationTest;

class SupervisorOptionsTest extends IntegrationTest
{
    public function test_default_queue_is_used_when_null_is_given()
    {
        $options = new SupervisorOptions('name', 'redis');
        $this->assertSame('default', $options->queue);
    }
}
