<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class AkeneoImportProductFilesCronModule implements SimpleCronModuleInterface
{
    /**
     * @param \App\Model\Product\Transfer\Akeneo\AkeneoImportAssemblyInstructionProductFilesFacade $akeneoImportAssemblyInstructionProductFilesFacade
     * @param \App\Model\Product\Transfer\Akeneo\AkeneoImportProductTypePlanProductFilesFacade $akeneoImportProductTypePlanProductFilesFacade
     */
    public function __construct(
        private AkeneoImportAssemblyInstructionProductFilesFacade $akeneoImportAssemblyInstructionProductFilesFacade,
        private AkeneoImportProductTypePlanProductFilesFacade $akeneoImportProductTypePlanProductFilesFacade,
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
        $this->akeneoImportAssemblyInstructionProductFilesFacade->runTransfer();
        $this->akeneoImportProductTypePlanProductFilesFacade->runTransfer();
    }
}
