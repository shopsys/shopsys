<?php

declare(strict_types=1);

namespace Tests\App\Test\Codeception\Helper;

use Codeception\Module;
use Shopsys\FrameworkBundle\Component\Doctrine\DatabaseConnectionCredentialsProvider;
use Tests\App\Test\Codeception\Module\Db;

class DatabaseHelper extends Module
{
    /**
     * {@inheritdoc}
     */
    public function _initialize(): void
    {
        /** @var \Tests\App\Test\Codeception\Module\Db $dbModule */
        $dbModule = $this->getModule(Db::class);
        /** @var \Tests\App\Test\Codeception\Helper\SymfonyHelper $symfonyHelper */
        $symfonyHelper = $this->getModule(SymfonyHelper::class);
        /** @var \Shopsys\FrameworkBundle\Component\Doctrine\DatabaseConnectionCredentialsProvider $databaseConnectionCredentialsProvider */
        $databaseConnectionCredentialsProvider = $symfonyHelper->grabServiceFromContainer(DatabaseConnectionCredentialsProvider::class);

        $dbModule->_reconfigure([
            'dsn' => $databaseConnectionCredentialsProvider->getConnectionDsn(),
            'user' => $databaseConnectionCredentialsProvider->getDatabaseUsername(),
            'password' => $databaseConnectionCredentialsProvider->getDatabasePassword(),
        ]);
    }
}
