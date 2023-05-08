<?php

declare(strict_types=1);

namespace Tests\App\Test;

use Psr\Container\ContainerInterface;

abstract class FunctionalTestCase extends WebTestCase
{
    /**
     * @return \Psr\Container\ContainerInterface
     */
    public function createContainer(): ContainerInterface
    {
        return self::getContainer()->get('test.service_container');
    }
}
