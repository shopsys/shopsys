<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileLocator;
use Shopsys\FrameworkBundle\Twig\FileThumbnail\FileThumbnailExtension;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class UploadedFileExtension extends AbstractExtension
{
    protected const FILE_NOT_FOUND_ICON_TYPE = 'not-found';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     * @param \Shopsys\FrameworkBundle\Twig\FileThumbnail\FileThumbnailExtension $fileThumbnailExtension
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileLocator $uploadedFileLocator
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly UploadedFileFacade $uploadedFileFacade,
        protected readonly FileThumbnailExtension $fileThumbnailExtension,
        protected readonly UploadedFileLocator $uploadedFileLocator
    ) {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('uploadedFileUrl', [$this, 'getUploadedFileUrl']),
            new TwigFunction('uploadedFilePreview', [$this, 'getUploadedFilePreviewHtml'], ['is_safe' => ['html']]),
            new TwigFunction('uploadedFileExists', [$this, 'uploadedFileExists']),
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $uploadedFile
     * @return string
     */
    public function getUploadedFileUrl(UploadedFile $uploadedFile): string
    {
        return $this->uploadedFileFacade->getUploadedFileUrl($this->domain->getCurrentDomainConfig(), $uploadedFile);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $uploadedFile
     * @return string
     */
    public function getUploadedFilePreviewHtml(UploadedFile $uploadedFile): string
    {
        $filepath = $this->uploadedFileFacade->getAbsoluteUploadedFileFilepath($uploadedFile);

        try {
            $fileThumbnailInfo = $this->fileThumbnailExtension->getFileThumbnailInfo($filepath);
        } catch (ImageNotFoundException $exception) {
            return $this->getUploadedFileIconHtml(static::FILE_NOT_FOUND_ICON_TYPE);
        }
        $uploadedFileIconType = $fileThumbnailInfo->getIconType();

        if ($uploadedFileIconType !== null) {
            return $this->getUploadedFileIconHtml($uploadedFileIconType);
        }

        return '<img src="' . $fileThumbnailInfo->getImageUri() . '"/>';
    }

    /**
     * @param string $uploadedFileIconType
     * @return string
     */
    protected function getUploadedFileIconHtml(string $uploadedFileIconType): string
    {
        $classes = [
            'svg',
            'svg-file-' . $uploadedFileIconType,
            'list-files__item__file__type',
            'list-files__item__file__type--' . $uploadedFileIconType,
            'text-no-decoration',
            'cursor-pointer',
        ];

        return '<i class="' . implode(' ', $classes) . '"></i>';
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $uploadedFile
     * @return bool
     */
    public function uploadedFileExists(UploadedFile $uploadedFile): bool
    {
        return $this->uploadedFileLocator->fileExists($uploadedFile);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'file_extension';
    }
}
