<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Override;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileFacade;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileLocator;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileLocator;
use Shopsys\FrameworkBundle\Twig\FileThumbnail\FileThumbnailExtension;
use Symfony\UX\Icons\IconRendererInterface;
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
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileFacade $customerUploadedFileFacade
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileLocator $customerUploadedFileLocator
     * @param \Symfony\UX\Icons\IconRendererInterface $iconRenderer
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly UploadedFileFacade $uploadedFileFacade,
        protected readonly FileThumbnailExtension $fileThumbnailExtension,
        protected readonly UploadedFileLocator $uploadedFileLocator,
        protected readonly CustomerUploadedFileFacade $customerUploadedFileFacade,
        protected readonly CustomerUploadedFileLocator $customerUploadedFileLocator,
        protected readonly IconRendererInterface $iconRenderer,
    ) {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('uploadedFileUrl', $this->getUploadedFileUrl(...)),
            new TwigFunction('uploadedFilePreview', $this->getUploadedFilePreviewHtml(...), ['is_safe' => ['html']]),
            new TwigFunction('uploadedFilePreviewWithLink', $this->getUploadedFilePreviewWithLinkHtml(...), ['is_safe' => ['html']]),
            new TwigFunction('uploadedFileExists', $this->uploadedFileExists(...)),
            new TwigFunction('customerUploadedFileUrl', $this->getCustomerUploadedFileUrl(...)),
            new TwigFunction('customerUploadedFileExists', $this->customerUploadedFileExists(...)),
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $uploadedFile
     * @return string
     */
    public function getUploadedFileUrl(UploadedFile $uploadedFile): string
    {
        return $this->uploadedFileFacade->getUploadedFileUrl($this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID), $uploadedFile);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile $customerUploadedFile
     * @return string
     */
    public function getCustomerUploadedFileUrl(CustomerUploadedFile $customerUploadedFile): string
    {
        return $this->customerUploadedFileFacade->getCustomerUploadedFileDownloadUrl($this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID), $customerUploadedFile);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile $customerUploadedFile
     * @return bool
     */
    public function customerUploadedFileExists(CustomerUploadedFile $customerUploadedFile): bool
    {
        return $this->customerUploadedFileLocator->fileExists($customerUploadedFile);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $uploadedFile
     * @param string|null $additionalClasses
     * @return string
     */
    public function getUploadedFilePreviewHtml(UploadedFile $uploadedFile, ?string $additionalClasses = null): string
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

        return sprintf('<img src="%s"%s>', $fileThumbnailInfo->getImageUri(), $additionalClasses !== null ? 'class="' . $additionalClasses . '"' : '');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $uploadedFile
     * @return string
     */
    public function getUploadedFilePreviewWithLinkHtml(UploadedFile $uploadedFile): string
    {
        $filePreviewClasses = 'w-100 h-100 object-contain';
        $containerClasses = 'd-flex align-items-center justify-content-center h-100 w-100';

        if ($this->uploadedFileExists($uploadedFile)) {
            return sprintf(
                '<a href="%s" download class="%s">%s</a>',
                $this->getUploadedFileUrl($uploadedFile),
                $containerClasses,
                $this->getUploadedFilePreviewHtml($uploadedFile, $filePreviewClasses),
            );
        }

        return sprintf(
            '<div class="%s">%s</div>',
            $containerClasses,
            $this->getUploadedFilePreviewHtml($uploadedFile, $filePreviewClasses),
        );
    }

    /**
     * @param string $uploadedFileIconType
     * @return string
     */
    protected function getUploadedFileIconHtml(string $uploadedFileIconType): string
    {
        return $this->iconRenderer->renderIcon('file-' . $uploadedFileIconType, [
            'class' => 'icon icon-lg file-icon-' . $uploadedFileIconType,
        ]);
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
