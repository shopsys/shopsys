<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use BadMethodCallException;
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
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade
     */
    protected $uploadedFileFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Twig\FileThumbnail\FileThumbnailExtension
     */
    protected $fileThumbnailExtension;

    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileLocator|null
     */
    protected ?UploadedFileLocator $uploadedFileLocator;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     * @param \Shopsys\FrameworkBundle\Twig\FileThumbnail\FileThumbnailExtension $fileThumbnailExtension
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileLocator|null $uploadedFileLocator
     */
    public function __construct(
        Domain $domain,
        UploadedFileFacade $uploadedFileFacade,
        FileThumbnailExtension $fileThumbnailExtension,
        ?UploadedFileLocator $uploadedFileLocator = null
    ) {
        $this->domain = $domain;
        $this->uploadedFileFacade = $uploadedFileFacade;
        $this->fileThumbnailExtension = $fileThumbnailExtension;
        $this->uploadedFileLocator = $uploadedFileLocator;
    }

    /**
     * @required
     * @internal This function will be replaced by constructor injection in next major
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileLocator $uploadedFileLocator
     */
    public function setUploadedFileLocator(UploadedFileLocator $uploadedFileLocator)
    {
        if ($this->uploadedFileLocator !== null && $this->uploadedFileLocator !== $uploadedFileLocator) {
            throw new BadMethodCallException(
                sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__)
            );
        }
        if ($this->uploadedFileLocator !== null) {
            return;
        }

        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );
        $this->uploadedFileLocator = $uploadedFileLocator;
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
