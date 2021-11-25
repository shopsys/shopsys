<?php

declare(strict_types=1);

namespace Tests\App\Test;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class ApplicationTestCase extends CommonTestCase
{
    protected static ?KernelBrowser $currentClient = null;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     * @inject
     */
    private EventDispatcherInterface $eventDispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        static::$currentClient = $this->getCurrentClient();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (static::$currentClient === null) {
            return;
        }

        static::$currentClient->enableReboot();
        static::$currentClient->getKernel()->shutdown();
        static::$currentClient = null;
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        $kernelBrowser = $this->getCurrentClient()->getContainer();

        return $kernelBrowser->get('test.service_container');
    }

    /**
     * Method is declared as final, so it's not unintentionally overridden by using SymfonyTestContainer trait
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    final public function createContainer(): ContainerInterface
    {
        return $this->getContainer();
    }

    /**
     * @return \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    public function getCurrentClient(): KernelBrowser
    {
        if (static::$currentClient === null) {
            static::$currentClient = static::createClient();
            static::$currentClient->disableReboot();
        }

        return static::$currentClient;
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
        $client = self::createClient($kernelOptions);

        /** @var \Symfony\Component\DependencyInjection\ContainerInterface $container */
        $container = $client->getContainer()->get('test.service_container');
        $container->get(Domain::class)->switchDomainById(Domain::FIRST_DOMAIN_ID);

        $serverOptions = $this->getClientServerParameters($username, $password, $clientOptions);
        $client->setServerParameters($serverOptions);

        $client->disableReboot();

        return $client;
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

        $serverOptions = $this->getClientServerParameters($username, $password, $clientOptions);
        $client->setServerParameters($serverOptions);

        return $client;
    }

    /**
     * @param string|null $username
     * @param string|null $password
     * @param array $clientOptions
     * @return array
     */
    private function getClientServerParameters(
        ?string $username,
        ?string $password,
        array $clientOptions
    ): array {
        $currentDomainUrl = $this->domain->getCurrentDomainConfig()->getUrl();

        $clientServerParameters = array_replace(
            [
                'HTTP_HOST' => preg_replace('#^https?://#', '', $currentDomainUrl),
                'HTTPS' => parse_url($currentDomainUrl, PHP_URL_SCHEME) === 'https',
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

    /**
     * Runs scheduled recalculations that would be executed on a kernel.response event
     * This allows to clean scheduled recalculations before making request on a client that could break the application
     * Eg. when testing GraphQL validation that breaks consistency of the entity and disallows any operation over it afterwards
     */
    protected function dispatchFakeKernelResponseEventToTriggerImmediateRecalculations(): void
    {
        $fakeKernelResponseEvent = new ResponseEvent(
            $this->getCurrentClient()->getKernel(),
            new Request(),
            HttpKernelInterface::MASTER_REQUEST,
            new Response()
        );

        /* @phpstan-ignore-next-line */
        $this->eventDispatcher->dispatch($fakeKernelResponseEvent, 'kernel.response');
    }
}
