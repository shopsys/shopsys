<?php

declare(strict_types=1);

namespace App\Component\Image\Kraken\Processing;

use App\Component\Image\Kraken\KrakenConfig;
use Kraken;
use League\Flysystem\FilesystemInterface;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig;

class ImageKrakenProcessor
{
    public const EXTENSION_JPEG = 'jpeg';
    public const EXTENSION_JPG = 'jpg';
    public const EXTENSION_PNG = 'png';
    public const EXTENSION_GIF = 'gif';
    public const EXTENSION_SVG = 'svg';

    /**
     * @var string[]
     */
    protected $supportedImageExtensions;

    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    private $filesystem;

    /**
     * @var \Kraken
     */
    private $kraken;

    /**
     * @var \App\Component\Image\Kraken\KrakenConfig
     */
    private $krakenConfig;

    /**
     * @param \League\Flysystem\FilesystemInterface $filesystem
     * @param \Kraken $kraken
     * @param \App\Component\Image\Kraken\KrakenConfig $krakenConfig
     */
    public function __construct(
        FilesystemInterface $filesystem,
        Kraken $kraken,
        KrakenConfig $krakenConfig
    ) {
        $this->filesystem = $filesystem;
        $this->kraken = $kraken;
        $this->krakenConfig = $krakenConfig;

        $this->supportedImageExtensions = [
            self::EXTENSION_JPEG,
            self::EXTENSION_JPG,
            self::EXTENSION_GIF,
            self::EXTENSION_PNG,
            self::EXTENSION_SVG,
        ];
    }

    /**
     * @param string $sourceImageFilepath
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig $sizeConfig
     * @return array
     */
    public function resizeBySizeConfig(string $sourceImageFilepath, ImageSizeConfig $sizeConfig): array
    {
        $tempImageFilePath = $this->getTempImageFilePath($sourceImageFilepath);
        $krakenImageData = $this->resizeAndWaitForResult($tempImageFilePath, $sizeConfig->getWidth(), $sizeConfig->getHeight(), $sizeConfig->getCrop());
        $this->removeTempImageFile($tempImageFilePath);

        return $krakenImageData;
    }

    /**
     * @param string $filepath
     * @return string
     */
    private function getTempImageFilePath($filepath): string
    {
        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));

        if (!in_array($extension, $this->supportedImageExtensions, true)) {
            throw new \Shopsys\FrameworkBundle\Component\Image\Processing\Exception\FileIsNotSupportedImageException($filepath);
        }
        try {
            if ($this->filesystem->has($filepath)) {
                $tempFileName = tempnam(sys_get_temp_dir(), 'krakenImage');
                file_put_contents($tempFileName, $this->filesystem->read($filepath));
                return $tempFileName;
            } else {
                throw new \Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException('File ' . $filepath . ' not found.');
            }
        } catch (\Intervention\Image\Exception\NotReadableException $ex) {
            throw new \Shopsys\FrameworkBundle\Component\Image\Processing\Exception\FileIsNotSupportedImageException($filepath, $ex);
        }
    }

    /**
     * @param string $filepath
     * @param int|null $width
     * @param int|null $height
     * @param bool $crop
     * @return array|null
     */
    private function resizeAndWaitForResult(string $filepath, ?int $width, ?int $height, bool $crop = false): ?array
    {
        $params = [
            'dev' => $this->krakenConfig->isSandbox(), // sandbox (random generating image from Kraken)
            'file' => $filepath,
            'wait' => true,
            'lossy' => $this->krakenConfig->isLossy(),
            'resize' => [
                'width' => $width,
                'height' => $height,
                'strategy' => $this->getImageStrategy($width, $height, $crop),
            ],
        ];

        return $this->kraken->upload($params);
    }

    /**
     * @param string $tempImageFilePath
     */
    private function removeTempImageFile(string $tempImageFilePath): void
    {
        if (file_exists($tempImageFilePath)) {
            unlink($tempImageFilePath);
        }
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->krakenConfig->isEnabled();
    }

    /**
     * @param int|null $width
     * @param int|null $height
     * @param bool $crop
     * @return string
     */
    private function getImageStrategy(?int $width, ?int $height, bool $crop): string
    {
        if ($crop === true) {
            $imageStrategy = 'crop';
        } elseif ($width === null && is_int($height)) {
            $imageStrategy = 'portrait';
        } elseif (is_int($width) && $height === null) {
            $imageStrategy = 'landscape';
        } else {
            $imageStrategy = 'auto';
        }

        return $imageStrategy;
    }
}
