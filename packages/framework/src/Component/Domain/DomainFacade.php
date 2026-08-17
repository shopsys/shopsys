<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Domain;

use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;

class DomainFacade
{
    public function __construct(
        protected readonly string $domainImagesDirectory,
        protected readonly DomainIconProcessor $domainIconProcessor,
        protected readonly FileUpload $fileUpload,
    ) {
    }

    public function editIcon(int $domainId, string $iconName): void
    {
        $temporaryFilepath = $this->fileUpload->getTemporaryFilepath($iconName);
        $this->domainIconProcessor->saveIcon(
            $domainId,
            $temporaryFilepath,
            $this->domainImagesDirectory,
        );
    }
}
