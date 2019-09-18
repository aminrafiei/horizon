<?php

namespace Aminrafiei\Horizon\Tests\Feature;

use Laravel\Facades\Config;
use Aminrafiei\Horizon\Horizon;
use Illuminate\Support\Facades\Redis;
use Aminrafiei\Horizon\Tests\IntegrationTest;

class RedisPrefixTest extends IntegrationTest
{
    public function test_prefix_can_be_configured()
    {
        config(['horizon.prefix' => 'custom:']);

        Horizon::use('default');

        $this->assertEquals('custom:', config('database.redis.horizon.options.prefix'));
    }
}
