<?php

declare(strict_types=1);

namespace Tests\App\Test;

use App\Component\Redis\RedisFacade;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Zalas\Injector\PHPUnit\TestCase\ServiceContainerTestCase;

abstract class CommonTestCase extends WebTestCase implements ServiceContainerTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade
     * @inject
     */
    private PersistentReferenceFacade $persistentReferenceFacade;

    /**
     * @var \App\Component\Redis\RedisFacade
     * @inject
     */
    private RedisFacade $redisFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     * @inject
     */
    protected Domain $domain;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     * @inject
     */
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();

        $this->domain->switchDomainById(Domain::FIRST_DOMAIN_ID);
    }

    protected function tearDown(): void
    {
        $this->redisFacade->closeAllClients();

        parent::tearDown();
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    abstract public function createContainer(): ContainerInterface;

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    abstract protected function getContainer(): ContainerInterface;

    /**
     * @param string $referenceName
     * @return object
     */
    protected function getReference(string $referenceName): object
    {
        return $this->persistentReferenceFacade->getReference($referenceName);
    }

    /**
     * @param string $referenceName
     * @param int $domainId
     * @return object
     */
    protected function getReferenceForDomain(string $referenceName, int $domainId): object
    {
        return $this->persistentReferenceFacade->getReferenceForDomain($referenceName, $domainId);
    }

    /**
     * @return string
     */
    public function getFirstDomainLocale(): string
    {
        return $this->domain->getLocale();
    }

    /**
     * @param string $routeName
     * @param array $parameters
     * @param int $absolute
     * @return string
     */
    protected function getLocalizedPathOnFirstDomainByRouteName(string $routeName, array $parameters = [], int $absolute = UrlGeneratorInterface::ABSOLUTE_URL): string
    {
        $domainRouterFactory = $this->getContainer()->get(DomainRouterFactory::class);
        $router = $domainRouterFactory->getRouter(Domain::FIRST_DOMAIN_ID);

        return $router->generate($routeName, $parameters, $absolute);
    }
}
