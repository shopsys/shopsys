<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Domain;

use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor;

class DomainFacade
{
    public function __construct(
        protected readonly string $domainImagesDirectory,
        protected readonly DomainIconProcessor $domainIconProcessor,
        protected readonly FilesystemOperator $filesystem,
        protected readonly FileUpload $fileUpload,
        protected readonly ImageProcessor $imageProcessor,
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

    public function existsDomainIcon(int $domainId): bool
    {
        return $this->filesystem->has($this->domainImagesDirectory . '/' . $domainId . '.png');
    }
}
