<?php

namespace Aminrafiei\Horizon\Tests\Controller;

use Aminrafiei\Horizon\Horizon;
use Aminrafiei\Horizon\Tests\IntegrationTest;

abstract class AbstractControllerTest extends IntegrationTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('app.key', 'base64:UTyp33UhGolgzCK5CJmT+hNHcA+dJyp3+oINtX+VoPI=');

        Horizon::auth(function () {
            return true;
        });
    }
}
