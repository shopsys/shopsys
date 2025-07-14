<?php

declare(strict_types=1);

namespace Tests\App\Test;

use Override;
use Psr\Container\ContainerInterface;

abstract class FunctionalTestCase extends WebTestCase
{
    /**
     * Method is declared as final, so it's not unintentionally overridden by using SymfonyTestContainer trait
     *
     * @return \Psr\Container\ContainerInterface
     */
    #[Override]
    final public function createContainer(): ContainerInterface
    {
        return self::getContainer()->get('test.service_container');
    }
}
