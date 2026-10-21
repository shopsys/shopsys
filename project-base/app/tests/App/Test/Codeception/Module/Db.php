<?php

declare(strict_types=1);

namespace Tests\App\Test\Codeception\Module;

use Codeception\Module\Db as BaseDb;
use Codeception\TestInterface;
use Shopsys\FrameworkBundle\Component\Doctrine\DatabaseConnectionCredentialsProvider;
use Shopsys\FrameworkBundle\Component\Doctrine\DatabaseSchemaFacade;
use Tests\App\Test\Codeception\Helper\SymfonyHelper;

class Db extends BaseDb
{
    /**
     * Revert database to the original state
     *
     * @param \Codeception\TestInterface $test
     */
    public function _after(TestInterface $test): void
    {
        $this->_loadDump();
    }

    public function cleanup()
    {
        /** @var \Tests\App\Test\Codeception\Helper\SymfonyHelper $symfonyHelper */
        $symfonyHelper = $this->getModule(SymfonyHelper::class);
        /** @var \Shopsys\FrameworkBundle\Component\Doctrine\DatabaseSchemaFacade $databaseSchemaFacade */
        $databaseSchemaFacade = $symfonyHelper->grabServiceFromContainer(DatabaseSchemaFacade::class);
        $databaseSchemaFacade->dropSchemaIfExists('public');
    }

    /**
     * {@inheritdoc}
     */
    public function _loadDump(?string $databaseKey = null, ?array $databaseConfig = null): void
    {
        $this->cleanup();

        parent::_loadDump($databaseKey, $this->getConfigWithConnectionCredentials($databaseConfig ?? $this->config));
    }

    /**
     * Codeception resets the module configuration after each test, so the connection credentials for the populator
     * command must be set every time the dump is loaded, and the dump path must be absolute as the command
     * is not executed from the Codeception root directory
     *
     * @param array $databaseConfig
     * @return array
     */
    protected function getConfigWithConnectionCredentials(array $databaseConfig): array
    {
        /** @var \Tests\App\Test\Codeception\Helper\SymfonyHelper $symfonyHelper */
        $symfonyHelper = $this->getModule(SymfonyHelper::class);
        /** @var \Shopsys\FrameworkBundle\Component\Doctrine\DatabaseConnectionCredentialsProvider $databaseConnectionCredentialsProvider */
        $databaseConnectionCredentialsProvider = $symfonyHelper->grabServiceFromContainer(DatabaseConnectionCredentialsProvider::class);

        return array_merge($databaseConfig, [
            'dsn' => $databaseConnectionCredentialsProvider->getConnectionDsn(),
            'user' => $databaseConnectionCredentialsProvider->getDatabaseUsername(),
            'password' => $databaseConnectionCredentialsProvider->getDatabasePassword(),
            'dump' => codecept_root_dir() . $this->_getConfig('dump'),
        ]);
    }
}
