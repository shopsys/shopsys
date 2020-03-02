<?php

declare(strict_types=1);

namespace App\Component\Akeneo\Transfer\MediaFiles;

use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class AkeneoMediaFilesCronModule implements SimpleCronModuleInterface
{
    /**
     * @var \App\Component\Akeneo\Transfer\MediaFiles\AkeneoImportMediaFilesFacade
     */
    private $akeneoImportMediaFilesFacade;

    /**
     * @param \App\Component\Akeneo\Transfer\MediaFiles\AkeneoImportMediaFilesFacade $akeneoImportMediaFilesFacade
     */
    public function __construct(AkeneoImportMediaFilesFacade $akeneoImportMediaFilesFacade)
    {
        $this->akeneoImportMediaFilesFacade = $akeneoImportMediaFilesFacade;
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
        $code = '/testingFileCode/';
        $fileName = 'test.pdf';
        $this->akeneoImportMediaFilesFacade->downloadMediaFile($code, '/assets/frontend/files/products/', $fileName);
    }
}
