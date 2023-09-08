<?php

declare(strict_types=1);

namespace App\Model\ProductVideo;

class ProductVideoDataFactory
{
    /**
     * @param \App\Model\ProductVideo\ProductVideoTranslationsRepository $videoTranslationsRepository
     */
    public function __construct(
        public readonly ProductVideoTranslationsRepository $videoTranslationsRepository,
    ) {
    }

    /**
     * @return \App\Model\ProductVideo\ProductVideoData
     */
    private function createInstance(): ProductVideoData
    {
        return new ProductVideoData();
    }

    /**
     * @param \App\Model\ProductVideo\ProductVideo $productVideo
     * @return \App\Model\ProductVideo\ProductVideoData
     */
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
