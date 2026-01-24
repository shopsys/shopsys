<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\FileUpload;

class FileForUpload
{
    public function __construct(
        protected string $temporaryFilename,
        protected string $fileClass,
        protected string $category,
        protected int $nameConventionType,
        protected ?string $targetDirectory = null,
    ) {
    }

    public function getTemporaryFilename(): string
    {
        return $this->temporaryFilename;
    }

    public function getFileClass(): string
    {
        return $this->fileClass;
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
