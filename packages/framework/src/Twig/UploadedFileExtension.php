<?php

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Twig\FileThumbnail\FileThumbnailExtension;
use Twig_Extension;
use Twig_SimpleFunction;

class UploadedFileExtension extends Twig_Extension
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade
     */
    private $uploadedFileFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Twig\FileThumbnail\FileThumbnailExtension
     */
    private $fileThumbnailExtension;

    public function __construct(
        Domain $domain,
        UploadedFileFacade $uploadedFileFacade,
        FileThumbnailExtension $fileThumbnailExtension
    ) {
        $this->domain = $domain;
        $this->uploadedFileFacade = $uploadedFileFacade;
        $this->fileThumbnailExtension = $fileThumbnailExtension;
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('hasUploadedFile', [$this, 'hasUploadedFile']),
            new Twig_SimpleFunction('uploadedFileUrl', [$this, 'getUploadedFileUrl']),
            new Twig_SimpleFunction('uploadedFilePreview', [$this, 'getUploadedFilePreviewHtml'], ['is_safe' => ['html']]),
            new Twig_SimpleFunction('getUploadedFile', [$this, 'getUploadedFileByEntity']),
        ];
    }

    public function hasUploadedFile(Object $entity): bool
    {
        return $this->uploadedFileFacade->hasUploadedFile($entity);
    }

    public function getUploadedFileUrl(Object $entity): string
    {
        $uploadedFile = $this->getUploadedFileByEntity($entity);

        return $this->uploadedFileFacade->getUploadedFileUrl($this->domain->getCurrentDomainConfig(), $uploadedFile);
    }

    public function getUploadedFilePreviewHtml(Object $entity): string
    {
        $uploadedFile = $this->getUploadedFileByEntity($entity);
        $filepath = $this->uploadedFileFacade->getAbsoluteUploadedFileFilepath($uploadedFile);
        $fileThumbnailInfo = $this->fileThumbnailExtension->getFileThumbnailInfo($filepath);

        if ($fileThumbnailInfo->getIconType() !== null) {
            $classes = [
                'svg',
                'svg-file-' . $fileThumbnailInfo->getIconType(),
                'list-images__item__image__type',
                'list-images__item__image__type--' . $fileThumbnailInfo->getIconType(),
                'text-no-decoration',
                'cursor-pointer',
            ];

            return '<i class="' . implode(' ', $classes) . '"></i>';
        } else {
            return '<img src="' . $fileThumbnailInfo->getImageUri() . '"/>';
        }
    }

    public function getUploadedFileByEntity(Object $entity): \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile
    {
        return $this->uploadedFileFacade->getUploadedFileByEntity($entity);
    }

    public function getName(): string
    {
        return 'file_extension';
    }
}
