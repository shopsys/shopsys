<?php

declare(strict_types=1);

namespace App\Model\ProductVideo;

use App\Model\Product\Product;

class ProductVideoFacade
{
    public const YOUTUBE_URL_HTTPS = 'https://www.youtube.com/watch?v=';
    public const YOUTUBE_URL_HTTP = 'http://www.youtube.com/watch?v=';

    public const YOUTUBE_LINKS_ARRAY = [
        self::YOUTUBE_URL_HTTP,
        self::YOUTUBE_URL_HTTPS,
    ];

    /**
     * @param \App\Model\ProductVideo\ProductVideoRepository $productVideoRepository
     * @param \App\Model\ProductVideo\ProductVideoTranslationsRepository $productVideoTranslationsRepository
     */
    public function __construct(
        private readonly ProductVideoRepository $productVideoRepository,
        private readonly ProductVideoTranslationsRepository $productVideoTranslationsRepository,
    ) {
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \App\Model\ProductVideo\ProductVideoData[] $productVideoDataList
     */
    public function saveProductVideosToProduct(Product $product, array $productVideoDataList): void
    {
        /** @var \App\Model\ProductVideo\ProductVideo[] $productVideos */
        $productVideos = $this->productVideoRepository->findByProductId($product->getId());

        $videoDataListToUpdate = array_filter($productVideoDataList, function (ProductVideoData $productVideoData) {
            return (bool)$productVideoData->id;
        });

        $videoDataListToCreate = array_filter($productVideoDataList, function (ProductVideoData $productVideoData): bool {
            return (bool)$productVideoData->id !== true;
        });

        $productVideosToRemove = [];

        foreach ($productVideos as $productVideo) {
            if (array_key_exists($productVideo->getId(), $videoDataListToUpdate)) {
                $productVideo->setVideoToken(
                    str_replace(self::YOUTUBE_LINKS_ARRAY, '', ($videoDataListToUpdate[$productVideo->getId()])->videoToken),
                );
                $this->cleanProductVideoTranslationsForProductVideo($productVideo);
                $this->persistVideoTranslations($videoDataListToUpdate[$productVideo->getId()], $productVideo);
            } else {
                $productVideosToRemove[] = $productVideo;
            }
        }

        foreach ($productVideosToRemove as $productVideoToRemove) {
            $this->cleanProductVideoTranslationsForProductVideo($productVideoToRemove);
            $this->productVideoRepository->em->remove($productVideoToRemove);
        }

        foreach ($videoDataListToCreate as $videoDataToCreate) {
            $productVideoEntity = new ProductVideo();
            $productVideoEntity->setProduct($product);
            $productVideoEntity->setVideoToken(str_replace(self::YOUTUBE_LINKS_ARRAY, '', $videoDataToCreate->videoToken));
            $this->productVideoRepository->em->persist($productVideoEntity);

            $this->persistVideoTranslations($videoDataToCreate, $productVideoEntity);
        }
        $this->productVideoRepository->em->flush();
    }

    /**
     * @param \App\Model\ProductVideo\ProductVideoData $videoDataToCreate
     * @param \App\Model\ProductVideo\ProductVideo $productVideoEntity
     */
    private function persistVideoTranslations(
        ProductVideoData $videoDataToCreate,
        ProductVideo $productVideoEntity,
    ): void {
        foreach ($videoDataToCreate->videoTokenDescriptions as $descriptionLocale => $descriptionValue) {
            $productVideoTranslation = new ProductVideoTranslations();
            $productVideoTranslation->setLocale($descriptionLocale);
            $productVideoTranslation->setDescription($descriptionValue ?? '');
            $productVideoTranslation->setProductVideo($productVideoEntity);
            $this->productVideoRepository->em->persist($productVideoTranslation);
        }
    }

    /**
     * @param \App\Model\ProductVideo\ProductVideo $productVideo
     */
    private function cleanProductVideoTranslationsForProductVideo(ProductVideo $productVideo): void
    {
        $productVideoTranslations = $this->productVideoTranslationsRepository->findByProductVideoId($productVideo->getId());

        foreach ($productVideoTranslations as $productVideoTranslation) {
            $this->productVideoTranslationsRepository->em->remove($productVideoTranslation);
        }
    }
}
