<?php

namespace Shopsys\FrameworkBundle\Twig\FileThumbnail;

use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\Image\Processing\ImageThumbnailFactory;
use Twig_Extension;
use Twig_SimpleFunction;

class FileThumbnailExtension extends Twig_Extension
{
    const DEFAULT_ICON_TYPE = 'all';
    const IMAGE_THUMBNAIL_QUALITY = 80;

    /**
     * @var string[]
     */
    private $iconsByExtension;

    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload
     */
    private $fileUpload;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\Processing\ImageThumbnailFactory
     */
    private $imageThumbnailFactory;

    public function __construct(FileUpload $fileUpload, ImageThumbnailFactory $imageThumbnailFactory)
    {
        $this->fileUpload = $fileUpload;
        $this->imageThumbnailFactory = $imageThumbnailFactory;
        $this->iconsByExtension = [
            'csv' => 'excel',
            'doc' => 'word',
            'docx' => 'word',
            'html' => 'xml',
            'ods' => 'excel',
            'odt' => 'word',
            'pdf' => 'pdf',
            'rtf' => 'word',
            'xls' => 'excel',
            'xlsx' => 'excel',
            'xhtml' => 'xml',
            'xml' => 'xml',
        ];
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('getFileThumbnailInfoByTemporaryFilename', [$this, 'getFileThumbnailInfoByTemporaryFilename']),
        ];
    }

    public function getName()
    {
        return 'file_thumbnail_extension';
    }

    /**
     * @param string $filepath
     * @return \Shopsys\FrameworkBundle\Twig\FileThumbnail\FileThumbnailInfo
     */
    public function getFileThumbnailInfo($filepath)
    {
        try {
            return $this->getImageThumbnailInfo($filepath);
        } catch (\Shopsys\FrameworkBundle\Component\Image\Processing\Exception\FileIsNotSupportedImageException $ex) {
            return new FileThumbnailInfo($this->getIconTypeByFilename($filepath));
        }
    }

    /**
     * @param string $temporaryFilename
     * @return \Shopsys\FrameworkBundle\Twig\FileThumbnail\FileThumbnailInfo
     */
    public function getFileThumbnailInfoByTemporaryFilename($temporaryFilename)
    {
        $filepath = $this->fileUpload->getTemporaryFilepath($temporaryFilename);

        return $this->getFileThumbnailInfo($filepath);
    }

    /**
     * @param string $filepath
     * @return \Shopsys\FrameworkBundle\Twig\FileThumbnail\FileThumbnailInfo
     */
    private function getImageThumbnailInfo($filepath)
    {
        $image = $this->imageThumbnailFactory->getImageThumbnail($filepath);

        return new FileThumbnailInfo(null, $image->encode('data-url', self::IMAGE_THUMBNAIL_QUALITY)->getEncoded());
    }

    /**
     * @param string $filepath
     * @return string
     */
    private function getIconTypeByFilename($filepath)
    {
        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        if (array_key_exists($extension, $this->iconsByExtension)) {
            return $this->iconsByExtension[$extension];
        }

        return self::DEFAULT_ICON_TYPE;
    }
}
