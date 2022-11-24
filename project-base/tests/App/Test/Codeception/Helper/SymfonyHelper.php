<?php

declare(strict_types=1);

namespace Tests\App\Test\Codeception\Helper;

use App\Kernel as AppKernel;
use Codeception\Configuration;
use Codeception\Module;
use Codeception\TestInterface;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Symfony\Component\HttpKernel\Kernel;

class SymfonyHelper extends Module
{
    /**
     * @var \Symfony\Component\HttpKernel\Kernel
     */
    private Kernel $kernel;

    /**
     * {@inheritDoc}
     */
    public function _initialize(): void
    {
        require_once Configuration::projectDir() . '/../app/autoload.php';

        $this->kernel = new AppKernel(EnvironmentType::ACCEPTANCE, EnvironmentType::isDebug(EnvironmentType::ACCEPTANCE));
        $this->kernel->boot();
    }

    /**
     * {@inheritDoc}
     */
    public function _before(TestInterface $test): void
    {
        $this->kernel->boot();
    }

    /**
     * @param string $serviceId
     * @return object
     */
    public function grabServiceFromContainer(string $serviceId): object
    {
        return $this->kernel->getContainer()->get('test.service_container')->get($serviceId);
    }
}
