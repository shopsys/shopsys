<?php

namespace Shopsys\FrameworkBundle\Component\Domain;

use League\Flysystem\FilesystemInterface;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;

class DomainFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\DomainIconResizer
     */
    protected $domainIconResizer;

    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $domainImagesDirectory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload
     */
    protected $fileUpload;

    /**
     * @param string $domainImagesDirectory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Domain\DomainIconResizer $domainIconResizer
     * @param \League\Flysystem\FilesystemInterface $fileSystem
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload $fileUpload
     */
    public function __construct(
        string $domainImagesDirectory,
        Domain $domain,
        DomainIconResizer $domainIconResizer,
        FilesystemInterface $fileSystem,
        FileUpload $fileUpload
    ) {
        $this->domainImagesDirectory = $domainImagesDirectory;
        $this->domain = $domain;
        $this->domainIconResizer = $domainIconResizer;
        $this->filesystem = $fileSystem;
        $this->fileUpload = $fileUpload;
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
        $this->domainIconResizer->convertToDomainIconFormatAndSave(
            $domainId,
            $temporaryFilepath,
            $this->domainImagesDirectory
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
