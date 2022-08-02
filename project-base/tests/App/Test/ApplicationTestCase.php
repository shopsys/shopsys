<?php

declare(strict_types=1);

namespace Tests\App\Test;

use Psr\Container\ContainerInterface as PsrContainerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\DependencyInjection\ContainerInterface;
use const PHP_URL_SCHEME;

abstract class ApplicationTestCase extends WebTestCase
{
    /**
     * @var \Tests\App\Test\Client|null
     */
    protected static ?Client $client = null;

    protected function setUp(): void
    {
        parent::setUp();

        self::$client = self::getCurrentClient();
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected static function getContainer(): ContainerInterface
    {
        return self::getCurrentClient()->getContainer()->get('test.service_container');
    }

    /**
     * @return \Psr\Container\ContainerInterface
     */
    public function createContainer(): PsrContainerInterface
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
     * @param array $kernelOptions
     * @param array $clientOptions
     * @return \Tests\App\Test\Client
     */
    protected function createNewClient(
        ?string $username = null,
        ?string $password = null,
        array $kernelOptions = [],
        array $clientOptions = []
    ): Client {
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
     * @return \Tests\App\Test\Client
     */
    protected function configureCurrentClient(
        ?string $username,
        ?string $password,
        array $clientOptions = []
    ): Client {
        $client = self::getCurrentClient();

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

    protected function tearDown(): void
    {
        parent::tearDown();

        if (static::$client === null) {
            return;
        }

        static::$client->enableReboot();
        static::$client->getKernel()->shutdown();
        static::$client = null;
    }
}
