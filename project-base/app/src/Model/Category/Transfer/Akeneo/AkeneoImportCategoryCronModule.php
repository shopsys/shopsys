<?php

declare(strict_types=1);

namespace App\Model\Category\Transfer\Akeneo;

use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class AkeneoImportCategoryCronModule implements SimpleCronModuleInterface
{
    /**
     * @param \App\Model\Category\Transfer\Akeneo\AkeneoImportCategoryFacade $akeneoImportCategoryFacade
     */
    public function __construct(
        private readonly AkeneoImportCategoryFacade $akeneoImportCategoryFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(Logger $logger): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        $this->akeneoImportCategoryFacade->runTransfer();
    }
}
