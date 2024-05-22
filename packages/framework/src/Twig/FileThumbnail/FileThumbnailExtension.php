<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig\FileThumbnail;

use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\Image\Processing\Exception\FileIsNotSupportedImageException;
use Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FileThumbnailExtension extends AbstractExtension
{
    public const string DEFAULT_ICON_TYPE = 'all';

    /**
     * @var string[]
     */
    protected array $iconsByExtension;

    /**
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload $fileUpload
     * @param \Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor $imageProcessor
     */
    public function __construct(
        protected readonly FileUpload $fileUpload,
        protected readonly ImageProcessor $imageProcessor,
    ) {
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
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'getFileThumbnailInfoByTemporaryFilename',
                [$this, 'getFileThumbnailInfoByTemporaryFilename'],
            ),
        ];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'file_thumbnail_extension';
    }

    /**
     * @param string $filepath
     * @return \Shopsys\FrameworkBundle\Twig\FileThumbnail\FileThumbnailInfo
     */
    public function getFileThumbnailInfo(string $filepath): FileThumbnailInfo
    {
        try {
            return $this->getImageThumbnailInfo($filepath);
        } catch (FileIsNotSupportedImageException $ex) {
            return new FileThumbnailInfo($this->getIconTypeByFilename($filepath));
        }
    }

    /**
     * @param string $temporaryFilename
     * @return \Shopsys\FrameworkBundle\Twig\FileThumbnail\FileThumbnailInfo
     */
    public function getFileThumbnailInfoByTemporaryFilename(string $temporaryFilename): FileThumbnailInfo
    {
        $filepath = $this->fileUpload->getTemporaryFilepath($temporaryFilename);

        return $this->getFileThumbnailInfo($filepath);
    }

    /**
     * @param string $filepath
     * @return \Shopsys\FrameworkBundle\Twig\FileThumbnail\FileThumbnailInfo
     */
    protected function getImageThumbnailInfo(string $filepath): FileThumbnailInfo
    {
        return new FileThumbnailInfo(null, $this->imageProcessor->getEncodedImageUri($filepath));
    }

    /**
     * @param string $filepath
     * @return string
     */
    protected function getIconTypeByFilename(string $filepath): string
    {
        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));

        if (array_key_exists($extension, $this->iconsByExtension)) {
            return $this->iconsByExtension[$extension];
        }

        return self::DEFAULT_ICON_TYPE;
    }
}
