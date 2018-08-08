<?php

namespace Tests\ShopBundle\Test;

use Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class FunctionalTestCase extends WebTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    private $client;

    protected function setUpDomain(): void
    {
        $domain = $this->getContainer()->get(Domain::class);
        /* @var $domain \Shopsys\FrameworkBundle\Component\Domain\Domain */
        $domain->switchDomainById(1);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDomain();
    }
    
    protected function getClient(
        bool $createNew = false,
        string $username = null,
        string $password = null,
        array $kernelOptions = []
    ): \Symfony\Bundle\FrameworkBundle\Client {
        $defaultKernelOptions = [
            'environment' => EnvironmentType::TEST,
            'debug' => EnvironmentType::isDebug(EnvironmentType::TEST),
        ];

        $kernelOptions = array_replace($defaultKernelOptions, $kernelOptions);

        if ($createNew) {
            $this->client = $this->createClient($kernelOptions);
            $this->setUpDomain();
        } elseif (!isset($this->client)) {
            $this->client = $this->createClient($kernelOptions);
        }

        if ($username !== null) {
            $this->client->setServerParameters([
                'PHP_AUTH_USER' => $username,
                'PHP_AUTH_PW' => $password,
            ]);
        }

        return $this->client;
    }

    protected function getContainer(): \Symfony\Component\DependencyInjection\ContainerInterface
    {
        return $this->getClient()->getContainer();
    }
    
    protected function getReference(string $referenceName): object
    {
        $persistentReferenceFacade = $this->getContainer()
            ->get(PersistentReferenceFacade::class);
        /* @var $persistentReferenceFacade \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade */

        return $persistentReferenceFacade->getReference($referenceName);
    }
}
