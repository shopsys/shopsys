<?php

declare(strict_types=1);

namespace App\Model\Category\Transfer\Akeneo;

use Psr\Log\LoggerInterface;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

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
    public function setLogger(LoggerInterface $logger)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->akeneoImportCategoryFacade->runTransfer();
    }
}
