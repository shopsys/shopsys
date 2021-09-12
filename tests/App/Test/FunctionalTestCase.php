<?php

declare(strict_types=1);

namespace Tests\App\Test;

use Symfony\Component\DependencyInjection\ContainerInterface;

class FunctionalTestCase extends CommonTestCase
{
    /**
     * Method is declared as final, so it's not unintentionally overridden by using SymfonyTestContainer trait
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    final public function createContainer(): ContainerInterface
    {
        if (self::$kernel === null) {
            static::bootKernel();
        }

        return self::$container;
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        return self::$container;
    }

    protected function skipTestIfFirstDomainIsNotInEnglish(): void
    {
        if ($this->getFirstDomainLocale() !== 'en') {
            $this->markTestSkipped('Tests for product searching are run only when the first domain has English locale');
        }
    }
}
