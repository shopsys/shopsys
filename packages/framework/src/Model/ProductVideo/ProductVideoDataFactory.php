<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductVideo;

class ProductVideoDataFactory
{
    public function __construct(
        protected readonly ProductVideoTranslationsRepository $videoTranslationsRepository,
    ) {
    }

    protected function createInstance(): ProductVideoData
    {
        return new ProductVideoData();
    }

    public function create(): ProductVideoData
    {
        return $this->createInstance();
    }

    public function createFromProductVideo(ProductVideo $productVideo): ProductVideoData
    {
        $productVideoData = $this->createInstance();

        $productVideoData->id = $productVideo->getId();
        $productVideoData->videoToken = $productVideo->getVideoToken();

        $mappedTranslations = [];

        foreach ($this->videoTranslationsRepository->findByProductVideoId($productVideo->getId()) as $videoTranslation) {
            $mappedTranslations[$videoTranslation->getLocale()] = $videoTranslation->getDescription();
        }

        $productVideoData->videoTokenDescriptions = $mappedTranslations;

        return $productVideoData;
    }
}
