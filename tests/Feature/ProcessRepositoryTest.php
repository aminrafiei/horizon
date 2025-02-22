<?php

namespace Aminrafiei\Horizon\Tests\Feature;

use Aminrafiei\Horizon\Tests\IntegrationTest;
use Aminrafiei\Horizon\Contracts\ProcessRepository;

class ProcessRepositoryTest extends IntegrationTest
{
    public function test_expired_orphans_can_be_found()
    {
        $repo = resolve(ProcessRepository::class);

        $repo->orphaned('foo', [1, 2, 3, 4, 5, 6]);
        sleep(2);
        $repo->orphaned('foo', [1, 2, 3]);

        $orphans = $repo->orphanedFor('foo', 1);

        $this->assertEquals([1, 2, 3], $orphans);
    }

    public function test_orphans_can_be_deleted()
    {
        $repo = resolve(ProcessRepository::class);
        $repo->orphaned('foo', [1, 2, 3]);
        $repo->forgetOrphans('foo', [1, 2, 3]);
        $this->assertEquals([], $repo->allOrphans('foo'));
    }
}
