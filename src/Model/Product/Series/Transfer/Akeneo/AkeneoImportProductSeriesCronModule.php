<?php

declare(strict_types=1);

namespace App\Model\Product\Series\Transfer\Akeneo;

use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class AkeneoImportProductSeriesCronModule implements SimpleCronModuleInterface
{
    /**
     * @var \App\Model\Product\Series\Transfer\Akeneo\AkeneoImportProductSeriesFacade
     */
    private $akeneoImportProductSeriesFacade;

    /**
     * @param \App\Model\Product\Series\Transfer\Akeneo\AkeneoImportProductSeriesFacade $akeneoImportProductSeriesFacade
     */
    public function __construct(AkeneoImportProductSeriesFacade $akeneoImportProductSeriesFacade)
    {
        $this->akeneoImportProductSeriesFacade = $akeneoImportProductSeriesFacade;
    }

    /**
     * @param \Symfony\Bridge\Monolog\Logger $logger
     */
    public function setLogger(Logger $logger)
    {
    }

    public function run()
    {
        $this->akeneoImportProductSeriesFacade->runTransfer();
    }
}
