<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Media;

use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;

class MediaDataFactory
{
    public function __construct(
        protected readonly ImageUploadDataFactory $imageUploadDataFactory,
    ) {
    }

    protected function createInstance(): MediaData
    {
        return new MediaData();
    }

    public function create(): MediaData
    {
        $mediaData = $this->createInstance();

        $mediaData->image = $this->imageUploadDataFactory->create();

        return $mediaData;
    }

    public function createFromMedia(Media $media): MediaData
    {
        $mediaData = $this->createInstance();
        $this->fillFromMedia($mediaData, $media);

        return $mediaData;
    }

    protected function fillFromMedia(MediaData $mediaData, Media $media): void
    {
        $mediaData->title = $media->getTitle();
        $mediaData->width = $media->getWidth();
        $mediaData->height = $media->getHeight();
        $mediaData->image = $this->imageUploadDataFactory->createFromEntityAndType($media);
    }
}
