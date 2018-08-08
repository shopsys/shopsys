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

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('getFileThumbnailInfoByTemporaryFilename', [$this, 'getFileThumbnailInfoByTemporaryFilename']),
        ];
    }

    public function getName(): string
    {
        return 'file_thumbnail_extension';
    }

    public function getFileThumbnailInfo(string $filepath): \Shopsys\FrameworkBundle\Twig\FileThumbnail\FileThumbnailInfo
    {
        try {
            return $this->getImageThumbnailInfo($filepath);
        } catch (\Shopsys\FrameworkBundle\Component\Image\Processing\Exception\FileIsNotSupportedImageException $ex) {
            return new FileThumbnailInfo($this->getIconTypeByFilename($filepath));
        }
    }

    public function getFileThumbnailInfoByTemporaryFilename(string $temporaryFilename): \Shopsys\FrameworkBundle\Twig\FileThumbnail\FileThumbnailInfo
    {
        $filepath = $this->fileUpload->getTemporaryFilepath($temporaryFilename);

        return $this->getFileThumbnailInfo($filepath);
    }

    private function getImageThumbnailInfo(string $filepath): \Shopsys\FrameworkBundle\Twig\FileThumbnail\FileThumbnailInfo
    {
        $image = $this->imageThumbnailFactory->getImageThumbnail($filepath);

        return new FileThumbnailInfo(null, $image->encode('data-url', self::IMAGE_THUMBNAIL_QUALITY)->getEncoded());
    }

    private function getIconTypeByFilename(string $filepath): string
    {
        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        if (array_key_exists($extension, $this->iconsByExtension)) {
            return $this->iconsByExtension[$extension];
        }

        return self::DEFAULT_ICON_TYPE;
    }
}
