<?php

declare(strict_types=1);

namespace Tests\App\Test;

use Psr\Container\ContainerInterface as PsrContainerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use const PHP_URL_SCHEME;

abstract class ApplicationTestCase extends WebTestCase
{
    /**
     * @inject
     */
    protected EntityManagerDecorator $em;

    /**
     * @inject
     */
    private EventDispatcherInterface $eventDispatcher;

    protected static ?Client $client = null;

    protected function setUp(): void
    {
        parent::setUp();

        self::$client = self::getCurrentClient();
        $this->em->beginTransaction();
        $this->em->getConnection()->setAutoCommit(false);
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected static function getContainer(): ContainerInterface
    {
        return self::getCurrentClient()->getContainer()->get('test.service_container');
    }

    /**
     * Method is declared as final, so it's not unintentionally overridden by using SymfonyTestContainer trait
     *
     * @return \Psr\Container\ContainerInterface
     */
    final public function createContainer(): PsrContainerInterface
    {
        return self::getContainer();
    }

    /**
     * @return \Tests\App\Test\Client
     */
    public static function getCurrentClient(): Client
    {
        if (static::$client === null) {
            static::$client = static::createClient();
            static::$client->disableReboot();
        }

        return static::$client;
    }

    /**
     * Creates a new Client with provided options, disabled reboot and Domain switched to ID 1
     * The Client will have its own Kernel and Container, with different instances of services
     * This means that it will not have access to changed DB data if your other Client has EM in transaction
     *
     * @param string|null $username
     * @param string|null $password
     * @param mixed[] $kernelOptions
     * @param mixed[] $clientOptions
     * @return \Tests\App\Test\Client
     */
    protected function createNewClient(
        ?string $username = null,
        ?string $password = null,
        array $kernelOptions = [],
        array $clientOptions = [],
    ): Client {
        self::ensureKernelShutdown();
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
     * @param mixed[] $clientOptions
     * @return \Tests\App\Test\Client
     */
    protected function configureCurrentClient(
        ?string $username,
        ?string $password,
        array $clientOptions = [],
    ): Client {
        $client = self::getCurrentClient();

        $serverOptions = $this->getClientServerParameters($username, $password, $clientOptions);
        $client->setServerParameters($serverOptions);

        return $client;
    }

    /**
     * @param string|null $username
     * @param string|null $password
     * @param mixed[] $clientOptions
     * @return mixed[]
     */
    private function getClientServerParameters(
        ?string $username,
        ?string $password,
        array $clientOptions,
    ): array {
        $currentDomainUrl = $this->domain->getCurrentDomainConfig()->getUrl();

        $clientServerParameters = array_replace(
            [
                'HTTP_HOST' => preg_replace('#^https?://#', '', $currentDomainUrl),
                'HTTPS' => parse_url($currentDomainUrl, PHP_URL_SCHEME) === 'https',
            ],
            $clientOptions,
        );

        if ($username !== null) {
            $clientServerParameters['PHP_AUTH_USER'] = $username;
            $clientServerParameters['PHP_AUTH_PW'] = $password;
        }

        return $clientServerParameters;
    }

    /**
     * Runs scheduled recalculations that would be executed on a kernel.response event
     * This allows to clean scheduled recalculations before making request on a client that could break the application
     * Eg. when testing GraphQL validation that breaks consistency of the entity and disallows any operation over it afterwards
     */
    protected function dispatchFakeKernelResponseEventToTriggerImmediateRecalculations(): void
    {
        $fakeKernelResponseEvent = new ResponseEvent(
            self::getCurrentClient()->getKernel(),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            new Response(),
        );

        $this->eventDispatcher->dispatch($fakeKernelResponseEvent, 'kernel.response');
    }

    protected function tearDown(): void
    {
        $this->em->rollback();

        if (static::$client === null) {
            return;
        }

        static::$client->enableReboot();
        static::$client->getKernel()->shutdown();
        static::$client = null;

        parent::tearDown();
    }
}
