<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\DataFixture;

use Doctrine\Common\DataFixtures\FixtureInterface;

abstract class AbstractReferenceFixture implements FixtureInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade
     */
    protected $persistentReferenceFacade;

    /**
     * @required
     * @param \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade $persistentReferenceFacade
     */
    public function autowirePersistentReferenceFacade(PersistentReferenceFacade $persistentReferenceFacade): void
    {
        $this->persistentReferenceFacade = $persistentReferenceFacade;
    }

    /**
     * @param string $name
     * @param object $object
     */
    public function addReference(string $name, object $object): void
    {
        $this->persistentReferenceFacade->persistReference($name, $object);
    }

    /**
     * @param string $name
     * @return object
     */
    public function getReference(string $name): object
    {
        return $this->persistentReferenceFacade->getReference($name);
    }

    /**
     * @param string $name
     * @param object $object
     * @param int $domainId
     */
    public function addReferenceForDomain(string $name, object $object, int $domainId): void
    {
        $this->persistentReferenceFacade->persistReferenceForDomain($name, $object, $domainId);
    }

    /**
     * @param string $name
     * @param int $domainId
     * @return object
     */
    public function getReferenceForDomain(string $name, int $domainId): object
    {
        return $this->persistentReferenceFacade->getReferenceForDomain($name, $domainId);
    }
}
