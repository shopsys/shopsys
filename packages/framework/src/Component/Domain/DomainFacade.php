<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Domain;

use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor;

class DomainFacade
{
    /**
     * @param string $domainImagesDirectory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Domain\DomainIconProcessor $domainIconProcessor
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload $fileUpload
     * @param \Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor $imageProcessor
     */
    public function __construct(
        protected readonly string $domainImagesDirectory,
        protected readonly Domain $domain,
        protected readonly DomainIconProcessor $domainIconProcessor,
        protected readonly FilesystemOperator $filesystem,
        protected readonly FileUpload $fileUpload,
        protected readonly ImageProcessor $imageProcessor,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    public function getAllDomainConfigs(): array
    {
        return $this->domain->getAll();
    }

    /**
     * @param int $domainId
     * @param string $iconName
     */
    public function editIcon(int $domainId, string $iconName): void
    {
        $temporaryFilepath = $this->fileUpload->getTemporaryFilepath($iconName);
        $this->domainIconProcessor->saveIcon(
            $domainId,
            $temporaryFilepath,
            $this->domainImagesDirectory,
        );
    }

    /**
     * @param int $domainId
     * @return bool
     */
    public function existsDomainIcon(int $domainId): bool
    {
        return $this->filesystem->has($this->domainImagesDirectory . '/' . $domainId . '.png');
    }
}
