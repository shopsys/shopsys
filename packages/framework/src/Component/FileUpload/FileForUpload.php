<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\FileUpload;

class FileForUpload
{
    protected string $temporaryFilename;

    protected string $fileClass;

    protected string $category;

    protected ?string $targetDirectory = null;

    protected int $nameConventionType;

    /**
     * @param string $temporaryFilename
     * @param string $fileClass
     * @param string $category
     * @param string|null $targetDirectory
     * @param int $nameConventionType
     */
    public function __construct($temporaryFilename, $fileClass, $category, $targetDirectory, $nameConventionType)
    {
        $this->temporaryFilename = $temporaryFilename;
        $this->fileClass = $fileClass;
        $this->category = $category;
        $this->targetDirectory = $targetDirectory;
        $this->nameConventionType = $nameConventionType;
    }

    /**
     * @return string
     */
    public function getTemporaryFilename()
    {
        return $this->temporaryFilename;
    }

    /**
     * @return string
     */
    public function getFileClass()
    {
        return $this->fileClass;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return string|null
     */
    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

    /**
     * @return int
     */
    public function getNameConventionType()
    {
        return $this->nameConventionType;
    }
}
