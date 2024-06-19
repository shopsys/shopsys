<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\DataFixture;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\DBAL\Driver\Middleware as MiddlewareInterface;
use Doctrine\DBAL\Logging\Middleware as LoggingMiddleware;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractReferenceFixture implements FixtureInterface
{
    protected PersistentReferenceFacade $persistentReferenceFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    #[Required]
    public function removeLoggingMiddlewareFromEntityManager(EntityManagerInterface $entityManager): void
    {
        $middlewaresWithoutLoggingMiddleware = array_values(array_filter(
            $entityManager->getConnection()->getConfiguration()->getMiddlewares(),
            fn (MiddlewareInterface $middleware) => !($middleware instanceof LoggingMiddleware),
        ));
        $entityManager->getConnection()->getConfiguration()->setMiddlewares($middlewaresWithoutLoggingMiddleware);
        $entityManager->clear();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade $persistentReferenceFacade
     */
    #[Required]
    public function autowirePersistentReferenceFacade(PersistentReferenceFacade $persistentReferenceFacade)
    {
        $this->persistentReferenceFacade = $persistentReferenceFacade;
    }

    /**
     * @param string $name
     * @param object $object
     */
    public function addReference($name, $object)
    {
        $this->persistentReferenceFacade->persistReference($name, $object);
    }

    /**
     * @template T
     * @param string $name
     * @param class-string<T>|null $entityClassName
     * @return T
     */
    public function getReference($name, ?string $entityClassName = null)
    {
        return $this->persistentReferenceFacade->getReference($name, $entityClassName);
    }

    /**
     * @param string $name
     * @param object $object
     * @param int $domainId
     */
    public function addReferenceForDomain(string $name, $object, int $domainId): void
    {
        $this->persistentReferenceFacade->persistReferenceForDomain($name, $object, $domainId);
    }

    /**
     * @template T
     * @param string $name
     * @param int $domainId
     * @param class-string<T>|null $entityClassName
     * @return T
     */
    public function getReferenceForDomain(string $name, int $domainId, ?string $entityClassName = null)
    {
        return $this->persistentReferenceFacade->getReferenceForDomain($name, $domainId, $entityClassName);
    }
}
