<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Domain;

use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;

class DomainFacade
{
    protected FilesystemOperator $filesystem;

    protected string $domainImagesDirectory;

    /**
     * @param mixed $domainImagesDirectory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Domain\DomainIconResizer $domainIconResizer
     * @param \League\Flysystem\FilesystemOperator $fileSystem
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload $fileUpload
     */
    public function __construct(
        $domainImagesDirectory,
        protected readonly Domain $domain,
        protected readonly DomainIconResizer $domainIconResizer,
        FilesystemOperator $fileSystem,
        protected readonly FileUpload $fileUpload,
    ) {
        $this->domainImagesDirectory = $domainImagesDirectory;
        $this->filesystem = $fileSystem;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    public function getAllDomainConfigs()
    {
        return $this->domain->getAll();
    }

    /**
     * @param int $domainId
     * @param string $iconName
     */
    public function editIcon($domainId, $iconName)
    {
        $temporaryFilepath = $this->fileUpload->getTemporaryFilepath($iconName);
        $this->domainIconResizer->convertToDomainIconFormatAndSave(
            $domainId,
            $temporaryFilepath,
            $this->domainImagesDirectory,
        );
    }

    /**
     * @param int $domainId
     * @return bool
     */
    public function existsDomainIcon($domainId)
    {
        return $this->filesystem->has($this->domainImagesDirectory . '/' . $domainId . '.png');
    }
}
