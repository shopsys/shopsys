<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Test;

use GraphQL\Executor\Promise\Adapter\SyncPromiseQueue;
use GraphQL\Executor\Promise\Promise;

class SyncPromiseResolver
{
    public static function resolve(Promise $promise): mixed
    {
        $resolved = null;

        $promise->then(function ($value) use (&$resolved): void {
            $resolved = $value;
        });

        SyncPromiseQueue::run();

        return $resolved;
    }
}
