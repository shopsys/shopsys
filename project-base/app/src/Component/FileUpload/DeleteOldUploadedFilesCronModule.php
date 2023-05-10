<?php

declare(strict_types=1);

namespace App\Component\FileUpload;

use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class DeleteOldUploadedFilesCronModule implements SimpleCronModuleInterface
{
    /**
     * @var \App\Component\FileUpload\FileUpload
     */
    private $fileUpload;

    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    private $logger;

    /**
     * @param \App\Component\FileUpload\FileUpload $fileUpload
     */
    public function __construct(FileUpload $fileUpload)
    {
        $this->fileUpload = $fileUpload;
    }

    /**
     * @param \Symfony\Bridge\Monolog\Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function run()
    {
        $count = $this->fileUpload->deleteOldUploadedFiles();
        $this->logger->info($count . ' files were deleted.');
    }
}
