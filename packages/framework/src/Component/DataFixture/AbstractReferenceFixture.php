<?php

namespace Shopsys\FrameworkBundle\Component\DataFixture;

use Doctrine\Common\DataFixtures\FixtureInterface;

abstract class AbstractReferenceFixture implements FixtureInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade
     */
    private $persistentReferenceFacade;

    public function autowirePersistentReferenceFacade(PersistentReferenceFacade $persistentReferenceFacade): void
    {
        $this->persistentReferenceFacade = $persistentReferenceFacade;
    }

    public function addReference(string $name, object $object, bool $persistent = true): void
    {
        if ($persistent) {
            $this->persistentReferenceFacade->persistReference($name, $object);
        }
    }

    public function setReference(string $name, object $object, bool $persistent = true): void
    {
        if ($persistent) {
            $this->persistentReferenceFacade->persistReference($name, $object);
        }
    }

    public function getReference(string $name): object
    {
        return $this->persistentReferenceFacade->getReference($name);
    }
}
