<?php

declare(strict_types=1);

namespace Tests\App\Test\Codeception\Module;

use Codeception\Module\Db as BaseDb;
use Codeception\TestInterface;
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

        parent::_loadDump($databaseKey, $databaseConfig);
    }
}
