<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductVideo;

class ProductVideoDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideoTranslationsRepository $videoTranslationsRepository
     */
    public function __construct(
        protected readonly ProductVideoTranslationsRepository $videoTranslationsRepository,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideoData
     */
    protected function createInstance(): ProductVideoData
    {
        return new ProductVideoData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideoData
     */
    public function create(): ProductVideoData
    {
        return $this->createInstance();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideo $productVideo
     * @return \Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideoData
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
