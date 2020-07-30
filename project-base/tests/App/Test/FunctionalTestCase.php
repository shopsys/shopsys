<?php

declare(strict_types=1);

namespace Tests\App\Test;

use Psr\Container\ContainerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;
use Zalas\Injector\PHPUnit\TestCase\ServiceContainerTestCase;

abstract class FunctionalTestCase extends WebTestCase implements ServiceContainerTestCase
{
    use SymfonyTestContainer;

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
     * @var \Shopsys\FrameworkBundle\Model\Pricing\PriceConverter
     * @inject
     */
    private $priceConverter;

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
            $this->client = $this->createClient($kernelOptions, $clientOptions);
            $this->setUpDomain();
        } elseif (!isset($this->client)) {
            $this->client = $this->createClient($kernelOptions, $clientOptions);
        }

        if ($username !== null) {
            $this->client->setServerParameters([
                'PHP_AUTH_USER' => $username,
                'PHP_AUTH_PW' => $password,
            ]);
        }

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
     * @return \Psr\Container\ContainerInterface
     */
    public function createContainer(): ContainerInterface
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
     * @return string
     */
    protected function getFirstDomainLocale(): string
    {
        return $this->domain->getLocale();
    }

    /**
     * @param string $routeName
     * @param array $parameters
     * @return string
     */
    protected function getLocalizedPathOnFirstDomainByRouteName(string $routeName, array $parameters = []): string
    {
        $domainRouterFactory = $this->getContainer()->get(DomainRouterFactory::class);
        $router = $domainRouterFactory->getRouter(Domain::FIRST_DOMAIN_ID);

        return $router->generate($routeName, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param string $price
     * @return string
     */
    protected function getPriceWithVatConvertedToDomainDefaultCurrency(string $price): string
    {
        $money = $this->priceConverter->convertPriceWithVatToPriceInDomainDefaultCurrency(Money::create($price), Domain::FIRST_DOMAIN_ID);

        return $money->getAmount();
    }

    /**
     * @param string $price
     * @return string
     */
    protected function getPriceWithoutVatConvertedToDomainDefaultCurrency(string $price): string
    {
        $money = $this->priceConverter->convertPriceWithoutVatToPriceInDomainDefaultCurrency(Money::create($price), Domain::FIRST_DOMAIN_ID);

        return $money->getAmount();
    }
}
