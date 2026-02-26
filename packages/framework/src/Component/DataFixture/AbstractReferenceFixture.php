<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\DataFixture;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Doctrine\ToggleableDebugDataHolder;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractReferenceFixture implements FixtureInterface
{
    protected PersistentReferenceFacade $persistentReferenceFacade;

    protected DomainsForDataFixtureProvider $domainsForDataFixtureProvider;

    protected ToggleableDebugDataHolder $toggleableDebugDataHolder;

    #[Required]
    public function autowireToggleableDebugDataHolder(ToggleableDebugDataHolder $toggleableDebugDataHolder): void
    {
        $this->toggleableDebugDataHolder = $toggleableDebugDataHolder;
    }

    #[Required]
    public function disableLoggingForEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->toggleableDebugDataHolder->disable();
        $entityManager->clear();
    }

    #[Required]
    public function autowirePersistentReferenceFacade(PersistentReferenceFacade $persistentReferenceFacade): void
    {
        $this->persistentReferenceFacade = $persistentReferenceFacade;
    }

    #[Required]
    public function autowireDomainsForDataFixturesProvider(
        DomainsForDataFixtureProvider $domainsForDataFixtureProvider,
    ): void {
        $this->domainsForDataFixtureProvider = $domainsForDataFixtureProvider;
    }

    public function addReference(string $name, object $object): void
    {
        $this->persistentReferenceFacade->persistReference($name, $object);
    }

    /**
     * @template T
     * @param class-string<T>|null $entityClassName
     * @return T
     */
    public function getReference(string $name, ?string $entityClassName = null)
    {
        return $this->persistentReferenceFacade->getReference($name, $entityClassName);
    }

    public function addReferenceForDomain(string $name, object $object, int $domainId): void
    {
        $this->persistentReferenceFacade->persistReferenceForDomain($name, $object, $domainId);
    }

    /**
     * @template T
     * @param class-string<T>|null $entityClassName
     * @return T
     */
    public function getReferenceForDomain(string $name, int $domainId, ?string $entityClassName = null)
    {
        return $this->persistentReferenceFacade->getReferenceForDomain($name, $domainId, $entityClassName);
    }
}
