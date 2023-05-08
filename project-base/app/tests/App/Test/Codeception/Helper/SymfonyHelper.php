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
    private Kernel $kernel;

    /**
     * {@inheritdoc}
     */
    public function _initialize()
    {
        require_once Configuration::projectDir() . '/../app/autoload.php';

        $this->kernel = new AppKernel(EnvironmentType::ACCEPTANCE, EnvironmentType::isDebug(EnvironmentType::ACCEPTANCE));
        $this->kernel->boot();
    }

    /**
     * {@inheritdoc}
     */
    public function _before(TestInterface $test)
    {
        $this->kernel->boot();
    }

    /**
     * @param string $serviceId
     * @return object
     */
    public function grabServiceFromContainer($serviceId)
    {
        return $this->kernel->getContainer()->get('test.service_container')->get($serviceId);
    }
}
