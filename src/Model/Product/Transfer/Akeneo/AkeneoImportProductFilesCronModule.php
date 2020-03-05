<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class AkeneoImportProductFilesCronModule implements SimpleCronModuleInterface
{
    /**
     * @var \App\Model\Product\Transfer\Akeneo\AkeneoImportAssemblyInstructionProductFilesFacade
     */
    private $akeneoImportProductFilesFacade;

    /**
     * @param \App\Model\Product\Transfer\Akeneo\AkeneoImportAssemblyInstructionProductFilesFacade $akeneoImportProductFilesFacade
     */
    public function __construct(AkeneoImportAssemblyInstructionProductFilesFacade $akeneoImportProductFilesFacade)
    {
        $this->akeneoImportProductFilesFacade = $akeneoImportProductFilesFacade;
    }

    /**
     * @inheritDoc
     */
    public function setLogger(Logger $logger)
    {
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        $this->akeneoImportProductFilesFacade->runTransfer();
    }
}
