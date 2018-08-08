<?php

namespace Shopsys\FrameworkBundle\Component\FileUpload;

class FileForUpload
{
    /**
     * @var string
     */
    private $temporaryFilename;

    /**
     * @var bool
     */
    private $isImage;

    /**
     * @var string
     */
    private $category;

    /**
     * @var string|null
     */
    private $targetDirectory;

    /**
     * @var int
     */
    private $nameConventionType;

    /**
     * @param string|null $targetDirectory
     */
    public function __construct(string $temporaryFilename, bool $isImage, string $category, ?string $targetDirectory, int $nameConventionType)
    {
        $this->temporaryFilename = $temporaryFilename;
        $this->isImage = $isImage;
        $this->category = $category;
        $this->targetDirectory = $targetDirectory;
        $this->nameConventionType = $nameConventionType;
    }

    public function getTemporaryFilename(): string
    {
        return $this->temporaryFilename;
    }

    public function isImage(): bool
    {
        return $this->isImage;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getTargetDirectory(): ?string
    {
        return $this->targetDirectory;
    }

    public function getNameConventionType(): int
    {
        return $this->nameConventionType;
    }
}
