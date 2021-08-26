<?php

declare(strict_types=1);

namespace Tests\App\Test;

use Psr\Container\ContainerInterface;
use Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;
use Zalas\Injector\PHPUnit\TestCase\ServiceContainerTestCase;

abstract class FunctionalTestCase extends WebTestCase implements ServiceContainerTestCase
{
    use SymfonyTestContainer;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    private KernelBrowser $client;

    /**
     * @var \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade
     * @inject
     */
    protected PersistentReferenceFacade $persistentReferenceFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     * @inject
     */
    protected Domain $domain;

    protected function tearDown(): void
    {
        if ($this->client !== null) {
            $this->client->getKernel()->shutdown();
            unset($this->client);
        }

        parent::tearDown();
    }

    /**
     * Returns the instance of currently used client; creates one if none exists
     *
     * @return \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    protected function getCurrentClient(): KernelBrowser
    {
        if (!isset($this->client)) {
            $this->client = $this->createNewClient();
        }

        return $this->client;
    }

    /**
     * Configures the instance of currently used client; creates one if none exists
     *
     * @param string|null $username
     * @param string|null $password
     * @param array $clientOptions
     * @return \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    protected function configureCurrentClient(
        ?string $username,
        ?string $password,
        array $clientOptions = []
    ): KernelBrowser {
        $client = $this->getCurrentClient();

        /** @var \Symfony\Component\DependencyInjection\ContainerInterface $container */
        $container = $client->getContainer()->get('test.service_container');

        $serverOptions = $this->getClientServerParameters($container, $username, $password, $clientOptions);
        $client->setServerParameters($serverOptions);

        return $client;
    }

    /**
     * Creates a new Client with provided options, disabled reboot and Domain switched to ID 1
     * The Client will have its own Kernel and Container, with different instances of services
     * This means that it will not have access to changed DB data if your other Client has EM in transaction
     *
     * @param string|null $username
     * @param string|null $password
     * @param array $kernelOptions
     * @param array $clientOptions
     * @return \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    protected function createNewClient(
        ?string $username = null,
        ?string $password = null,
        array $kernelOptions = [],
        array $clientOptions = []
    ): KernelBrowser {
        $defaultKernelOptions = [
            'environment' => EnvironmentType::TEST,
            'debug' => EnvironmentType::isDebug(EnvironmentType::TEST),
        ];
        $kernelOptions = array_replace($defaultKernelOptions, $kernelOptions);

        $client = self::createClient($kernelOptions);

        /** @var \Symfony\Component\DependencyInjection\ContainerInterface $container */
        $container = $client->getContainer()->get('test.service_container');

        $container->get(Domain::class)->switchDomainById(Domain::FIRST_DOMAIN_ID);

        $serverOptions = $this->getClientServerParameters($container, $username, $password, $clientOptions);
        $client->setServerParameters($serverOptions);

        $client->disableReboot();

        return $client;
    }

    /**
     * @param \Psr\Container\ContainerInterface $container
     * @param string|null $username
     * @param string|null $password
     * @param array $clientOptions
     * @return array
     */
    private function getClientServerParameters(
        ContainerInterface $container,
        ?string $username,
        ?string $password,
        array $clientOptions
    ): array {
        $currentDomainUrl = $container->get(Domain::class)->getCurrentDomainConfig()->getUrl();

        $clientServerParameters = array_replace(
            [
                'HTTP_HOST' => preg_replace('#^https?://#', '', $currentDomainUrl),
                'HTTPS' => parse_url($currentDomainUrl, PHP_URL_SCHEME) === 'https'
            ],
            $clientOptions
        );

        if ($username !== null) {
            $clientServerParameters['PHP_AUTH_USER'] = $username;
            $clientServerParameters['PHP_AUTH_PW'] = $password;
        }

        return $clientServerParameters;
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        return $this->getCurrentClient()->getContainer()->get('test.service_container');
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
     * Method is declared as final, so it's not unintentionally overridden by using SymfonyTestContainer trait
     *
     * @return \Psr\Container\ContainerInterface
     */
    final public function createContainer(): ContainerInterface
    {
        return $this->getContainer()->get('test.service_container');
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

    protected function skipTestIfFirstDomainIsNotInEnglish(): void
    {
        if ($this->getFirstDomainLocale() !== 'en') {
            $this->markTestSkipped('Tests for product searching are run only when the first domain has English locale');
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
