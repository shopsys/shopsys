<?php

declare(strict_types=1);

namespace Tests\App\Test;

use Psr\Container\ContainerInterface;
use Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Zalas\Injector\PHPUnit\TestCase\ServiceContainerTestCase;

abstract class WebTestCase extends BaseWebTestCase implements ServiceContainerTestCase
{
    /**
     * @inject
     */
    private PersistentReferenceFacade $persistentReferenceFacade;

    /**
     * @inject
     */
    protected Domain $domain;

    protected function setUp(): void
    {
        parent::setUp();

        $this->domain->switchDomainById(Domain::FIRST_DOMAIN_ID);
    }

    /**
     * @return \Psr\Container\ContainerInterface
     */
    abstract public function createContainer(): ContainerInterface;

    /**
     * @param array<string, mixed> $options
     * @param array<string, mixed> $server
     * @return \Tests\App\Test\Client
     */
    protected static function createClient(array $options = [], array $server = []): Client
    {
        /** @var \Tests\App\Test\Client $client */
        $client = parent::createClient($options, $server);

        return $client;
    }

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
     * @param string $routeName
     * @param array<string, mixed> $parameters
     * @return string
     */
    protected function getLocalizedPathOnFirstDomainByRouteName(string $routeName, array $parameters = []): string
    {
        $domainRouterFactory = self::getContainer()->get(DomainRouterFactory::class);

        return $domainRouterFactory->getRouter(Domain::FIRST_DOMAIN_ID)
            ->generate($routeName, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    protected function skipTestIfFirstDomainIsNotInEnglish(): void
    {
        if ($this->getFirstDomainLocale() !== 'en') {
            $this->markTestSkipped(
                'Tests for product searching are run only when the first domain has English locale',
            );
        }
    }

    /**
     * We can use the shorthand here as $this->domain->switchDomainById(1) is called in setUp()
     *
     * @return string
     */
    protected function getFirstDomainLocale(): string
    {
        return $this->domain->getLocale();
    }
}
