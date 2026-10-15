<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Media;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Image\ImageUrlWithSizeHelper;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Webmozart\Assert\Assert;

class MediaFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly MediaRepository $mediaRepository,
        protected readonly MediaFactory $mediaFactory,
        protected readonly ImageFacade $imageFacade,
        protected readonly ImageUrlWithSizeHelper $imageUrlWithSizeHelper,
        protected readonly FileUpload $fileUpload,
    ) {
    }

    public function getById(int $mediaId): Media
    {
        return $this->mediaRepository->getById($mediaId);
    }

    public function getPaginatedByTitle(string $search, int $page, int $limit): PaginationResult
    {
        return $this->mediaRepository->getPaginatedByTitle($search, $page, $limit);
    }

    public function create(MediaData $mediaData): Media
    {
        $this->setDimensionsFromUploadedImage($mediaData);

        $media = $this->mediaFactory->create($mediaData);

        $this->em->persist($media);
        $this->em->flush();

        $this->imageFacade->manageImages($media, $mediaData->image);
        $this->em->flush();

        return $media;
    }

    public function edit(Media $media, MediaData $mediaData): void
    {
        $this->setDimensionsFromUploadedImage($mediaData);

        $media->edit($mediaData);

        $this->imageFacade->manageImages($media, $mediaData->image);
        $this->em->flush();
    }

    public function delete(Media $media): void
    {
        $this->em->remove($media);
        $this->em->flush();
    }

    public function getImageUrl(DomainConfig $domainConfig, Media $media, ?int $width = null): string
    {
        $imageUrl = $this->imageFacade->getImageUrl($domainConfig, $media);

        if ($width === null) {
            return $imageUrl;
        }

        return $this->imageUrlWithSizeHelper->limitSizeInImageUrl($imageUrl, $width);
    }

    protected function setDimensionsFromUploadedImage(MediaData $mediaData): void
    {
        if (count($mediaData->image->uploadedFiles) === 0) {
            return;
        }

        // the last uploaded file is the one that ends up as the media image
        $temporaryFilename = array_last($mediaData->image->uploadedFiles);
        $imageSize = getimagesize($this->fileUpload->getAbsoluteTemporaryFilepath($temporaryFilename));
        Assert::isArray($imageSize, 'Uploaded file is not a readable image.');

        $mediaData->width = $imageSize[0];
        $mediaData->height = $imageSize[1];
    }
}
