<?php

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FileUploadExtension extends AbstractExtension
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload
     */
    protected $fileUpload;

    /**
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload $fileUpload
     */
    public function __construct(FileUpload $fileUpload)
    {
        $this->fileUpload = $fileUpload;
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('getLabelByTemporaryFilename', [$this, 'getLabelByTemporaryFilename']),
        ];
    }

    /**
     * @param string $temporaryFilename
     * @return string
     */
    public function getLabelByTemporaryFilename($temporaryFilename)
    {
        $filename = $this->fileUpload->getOriginalFilenameByTemporary($temporaryFilename);
        $filepath = ($this->fileUpload->getTemporaryDirectory() . '/' . $temporaryFilename);
        if (file_exists($filepath) && is_file($filepath) && is_writable($filepath)) {
            $fileSize = round((int)filesize($filepath) / 1000 / 1000, 2); //https://en.wikipedia.org/wiki/Binary_prefix
            return $filename . ' (' . $fileSize . ' MB)';
        }
        return '';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'fileupload_extension';
    }
}
