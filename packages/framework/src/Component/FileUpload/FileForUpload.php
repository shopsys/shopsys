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
     * @param string $temporaryFilename
     * @param bool $isImage
     * @param string $category
     * @param string|null $targetDirectory
     * @param int $nameConventionType
     */
    public function __construct($temporaryFilename, $isImage, $category, $targetDirectory, $nameConventionType)
    {
        $this->temporaryFilename = $temporaryFilename;
        $this->isImage = $isImage;
        $this->category = $category;
        $this->targetDirectory = $targetDirectory;
        $this->nameConventionType = $nameConventionType;
    }

    public function getTemporaryFilename()
    {
        return $this->temporaryFilename;
    }

    public function isImage()
    {
        return $this->isImage;
    }

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

    public function getNameConventionType()
    {
        return $this->nameConventionType;
    }
}
