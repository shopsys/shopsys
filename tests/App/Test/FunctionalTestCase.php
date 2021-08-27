<?php

declare(strict_types=1);

namespace Tests\App\Test;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;
use Zalas\Injector\PHPUnit\TestCase\ServiceContainerTestCase;

abstract class FunctionalTestCase extends WebTestCase implements ServiceContainerTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    private $client;

    /**
     * @var \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade
     * @inject
     */
    protected $persistentReferenceFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     * @inject
     */
    protected $domain;

    protected function setUpDomain()
    {
        $this->domain->switchDomainById(Domain::FIRST_DOMAIN_ID);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDomain();
    }

    /**
     * @var string[]|null
     */
    private static $phpUnitTestCaseProperties = null;

    /**
     * @return string[]
     */
    private static function getPhpUnitTestCaseProperties(): array
    {
        if (self::$phpUnitTestCaseProperties === null) {
            self::$phpUnitTestCaseProperties = [];

            $testCaseReflectionClass = new ReflectionClass(TestCase::class);
            $properties = $testCaseReflectionClass->getProperties();
            foreach ($properties as $property) {
                self::$phpUnitTestCaseProperties[] = $property->getName();
            }
        }

        return self::$phpUnitTestCaseProperties;
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $reflectionClass = new ReflectionClass($this);
        $properties = $reflectionClass->getProperties();
        $excludedProperties = self::getPhpUnitTestCaseProperties();
        foreach ($properties as $property) {
            if (in_array($property->getName(), $excludedProperties, true) === false) {
                $property->setAccessible(true);
                $property->setValue($this, null);
            }
        }
    }

    /**
     * @param bool $createNew
     * @param string $username
     * @param string $password
     * @param array $kernelOptions
     * @param array $clientOptions
     * @return \Symfony\Bundle\FrameworkBundle\Client
     */
    protected function findClient(
        $createNew = false,
        $username = null,
        $password = null,
        $kernelOptions = [],
        $clientOptions = []
    ) {
        $defaultKernelOptions = [
            'environment' => EnvironmentType::TEST,
            'debug' => EnvironmentType::isDebug(EnvironmentType::TEST),
        ];

        $kernelOptions = array_replace($defaultKernelOptions, $kernelOptions);

        if ($createNew) {
            $this->client = self::createClient($kernelOptions, $clientOptions);
            $this->setUpDomain();
        } elseif (!isset($this->client)) {
            $this->client = self::createClient($kernelOptions, $clientOptions);
        }

        if ($username !== null) {
            $this->client->setServerParameters([
                'PHP_AUTH_USER' => $username,
                'PHP_AUTH_PW' => $password,
            ]);
        }

        $this->client->disableReboot();

        return $this->client;
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->findClient()->getContainer()->get('test.service_container');
    }

    /**
     * @param string $referenceName
     * @return object
     */
    protected function getReference($referenceName)
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
    protected function getReferenceForDomain(string $referenceName, int $domainId)
    {
        return $this->persistentReferenceFacade->getReferenceForDomain($referenceName, $domainId);
    }

    protected function skipTestIfFirstDomainIsNotInEnglish()
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
