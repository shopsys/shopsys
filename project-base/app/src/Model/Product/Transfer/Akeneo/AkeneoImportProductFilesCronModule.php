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
    private $akeneoImportAssemblyInstructionProductFilesFacade;

    /**
     * @var \App\Model\Product\Transfer\Akeneo\AkeneoImportProductTypePlanProductFilesFacade
     */
    private $akeneoImportProductTypePlanProductFilesFacade;

    /**
     * @param \App\Model\Product\Transfer\Akeneo\AkeneoImportAssemblyInstructionProductFilesFacade $akeneoImportAssemblyInstructionProductFilesFacade
     * @param \App\Model\Product\Transfer\Akeneo\AkeneoImportProductTypePlanProductFilesFacade $akeneoImportProductTypePlanProductFilesFacade
     */
    public function __construct(
        AkeneoImportAssemblyInstructionProductFilesFacade $akeneoImportAssemblyInstructionProductFilesFacade,
        AkeneoImportProductTypePlanProductFilesFacade $akeneoImportProductTypePlanProductFilesFacade
    ) {
        $this->akeneoImportAssemblyInstructionProductFilesFacade = $akeneoImportAssemblyInstructionProductFilesFacade;
        $this->akeneoImportProductTypePlanProductFilesFacade = $akeneoImportProductTypePlanProductFilesFacade;
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
        $this->akeneoImportAssemblyInstructionProductFilesFacade->runTransfer();
        $this->akeneoImportProductTypePlanProductFilesFacade->runTransfer();
    }
}
