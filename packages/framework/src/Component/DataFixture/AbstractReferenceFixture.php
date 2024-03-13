<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\DataFixture;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\DBAL\Driver\Middleware as MiddlewareInterface;
use Doctrine\DBAL\Logging\Middleware as LoggingMiddleware;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractReferenceFixture implements FixtureInterface
{
    protected PersistentReferenceFacade $persistentReferenceFacade;

    /**
     * @required
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
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
     * @required
     * @param \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade $persistentReferenceFacade
     */
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
     * @param string $name
     * @return object
     */
    public function getReference($name)
    {
        return $this->persistentReferenceFacade->getReference($name);
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
     * @param string $name
     * @param int $domainId
     * @return object
     */
    public function getReferenceForDomain(string $name, int $domainId)
    {
        return $this->persistentReferenceFacade->getReferenceForDomain($name, $domainId);
    }
}
