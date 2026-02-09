<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\FileUpload;

use Monolog\Logger;
use Override;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class DeleteOldUploadedFilesCronModule implements SimpleCronModuleInterface
{
    protected Logger $logger;

    public function __construct(protected readonly FileUpload $fileUpload)
    {
    }

    #[Override]
    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    #[Override]
    public function run(): void
    {
        $count = $this->fileUpload->deleteOldUploadedFiles();

        $this->logger->info($count . ' files were deleted.');
    }
}
