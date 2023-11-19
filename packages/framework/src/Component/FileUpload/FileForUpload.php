<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\FileUpload;

class FileForUpload
{
    protected string $temporaryFilename;

    protected bool $isImage;

    protected string $category;

    protected ?string $targetDirectory = null;

    protected int $nameConventionType;

    /**
     * @param string $temporaryFilename
     * @param bool $isImage
     * @param string $category
     * @param string|null $targetDirectory
     * @param int $nameConventionType
     */
    public function __construct(string $temporaryFilename, bool $isImage, string $category, ?string $targetDirectory, int $nameConventionType)
    {
        $this->temporaryFilename = $temporaryFilename;
        $this->isImage = $isImage;
        $this->category = $category;
        $this->targetDirectory = $targetDirectory;
        $this->nameConventionType = $nameConventionType;
    }

    /**
     * @return string
     */
    public function getTemporaryFilename(): string
    {
        return $this->temporaryFilename;
    }

    /**
     * @return bool
     */
    public function isImage(): bool
    {
        return $this->isImage;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @return string|null
     */
    public function getTargetDirectory(): ?string
    {
        return $this->targetDirectory;
    }

    /**
     * @return int
     */
    public function getNameConventionType(): int
    {
        return $this->nameConventionType;
    }
}
