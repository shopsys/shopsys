<?php

declare(strict_types=1);

namespace App\Model\Category\Transfer\Akeneo;

use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class AkeneoImportCategoryCronModule implements SimpleCronModuleInterface
{
    /**
     * @var \App\Model\Category\Transfer\Akeneo\AkeneoImportCategoryFacade
     */
    private $akeneoImportCategoryFacade;

    /**
     * @param \App\Model\Category\Transfer\Akeneo\AkeneoImportCategoryFacade $akeneoImportCategoryFacade
     */
    public function __construct(AkeneoImportCategoryFacade $akeneoImportCategoryFacade)
    {
        $this->akeneoImportCategoryFacade = $akeneoImportCategoryFacade;
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(Logger $logger)
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
