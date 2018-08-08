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
     * @var \Shopsys\FrameworkBundle\Component\Domain\DomainService
     */
    protected $domainService;

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

    public function __construct(
        $domainImagesDirectory,
        Domain $domain,
        DomainService $domainService,
        FilesystemInterface $fileSystem,
        FileUpload $fileUpload
    ) {
        $this->domainImagesDirectory = $domainImagesDirectory;
        $this->domain = $domain;
        $this->domainService = $domainService;
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
    
    public function editIcon(int $domainId, string $iconName): void
    {
        $temporaryFilepath = $this->fileUpload->getTemporaryFilepath($iconName);
        $this->domainService->convertToDomainIconFormatAndSave($domainId, $temporaryFilepath, $this->domainImagesDirectory);
    }
    
    public function existsDomainIcon(int $domainId): bool
    {
        return $this->filesystem->has($this->domainImagesDirectory . '/' . $domainId . '.png');
    }
}
