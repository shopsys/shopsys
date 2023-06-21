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
        $productStoreData = $this->createInstance();

        $productStoreData->id = $productVideo->getId();
        $productStoreData->videoToken = $productVideo->getVideoToken();

        $mappedTranslations = [];

        foreach ($this->videoTranslationsRepository->findByProductVideoId($productVideo->getId()) as $videoTranslation) {
            $mappedTranslations[$videoTranslation->getLocale()] = $videoTranslation->getDescription();
        }

        $productStoreData->videoTokenDescriptions = $mappedTranslations;

        return $productStoreData;
    }
}
