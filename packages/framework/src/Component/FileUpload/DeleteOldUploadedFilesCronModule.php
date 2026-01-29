<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\FileUpload;

use Psr\Log\LoggerInterface;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class DeleteOldUploadedFilesCronModule implements SimpleCronModuleInterface
{
    protected LoggerInterface $logger;

    /**
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload $fileUpload
     */
    public function __construct(protected readonly FileUpload $fileUpload)
    {
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function run(): void
    {
        $count = $this->fileUpload->deleteOldUploadedFiles();

        $this->logger->info($count . ' files were deleted.');
    }
}
