<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductVideo;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductVideoFacade
{
    public const string YOUTUBE_URL_HTTPS = 'https://www.youtube.com/watch?v=';
    public const string YOUTUBE_URL_HTTP = 'http://www.youtube.com/watch?v=';

    public const array YOUTUBE_LINKS_ARRAY = [
        self::YOUTUBE_URL_HTTP,
        self::YOUTUBE_URL_HTTPS,
    ];

    public function __construct(
        protected readonly ProductVideoRepository $productVideoRepository,
        protected readonly ProductVideoTranslationsRepository $productVideoTranslationsRepository,
        protected readonly ProductVideoFactory $productVideoFactory,
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideoData[] $productVideoDataList
     */
    public function saveProductVideosToProduct(Product $product, array $productVideoDataList): void
    {
        $existingProductVideos = $this->productVideoRepository->findByProductId($product->getId());

        // Separate videos by whether they have IDs (existing) or not (new)
        $videoDataToUpdateById = [];
        $videoDataListToCreate = [];

        foreach ($productVideoDataList as $videoData) {
            if ($videoData->id !== null) {
                $videoDataToUpdateById[$videoData->id] = $videoData;
            } else {
                $videoDataListToCreate[] = $videoData;
            }
        }

        // Determine which existing videos to remove (not present in update data)
        $productVideosToRemove = [];

        foreach ($existingProductVideos as $existingVideo) {
            if (array_key_exists($existingVideo->getId(), $videoDataToUpdateById)) {
                // Update existing video
                $updateData = $videoDataToUpdateById[$existingVideo->getId()];
                $existingVideo->setVideoToken(
                    str_replace(self::YOUTUBE_LINKS_ARRAY, '', $updateData->videoToken),
                );
                $this->cleanProductVideoTranslationsForProductVideo($existingVideo);
                $this->persistVideoTranslations($updateData, $existingVideo);
            } else {
                // Mark for removal - video not present in form data
                $productVideosToRemove[] = $existingVideo;
            }
        }

        // Remove videos that are no longer present
        foreach ($productVideosToRemove as $productVideoToRemove) {
            $this->cleanProductVideoTranslationsForProductVideo($productVideoToRemove);
            $this->em->remove($productVideoToRemove);
        }

        // Create new videos
        foreach ($videoDataListToCreate as $videoDataToCreate) {
            $productVideoEntity = $this->productVideoFactory->create($videoDataToCreate);
            $productVideoEntity->setProduct($product);
            $productVideoEntity->setVideoToken(str_replace(self::YOUTUBE_LINKS_ARRAY, '', $videoDataToCreate->videoToken));
            $this->em->persist($productVideoEntity);

            $this->persistVideoTranslations($videoDataToCreate, $productVideoEntity);
        }

        $this->em->flush();
    }

    protected function persistVideoTranslations(
        ProductVideoData $videoDataToCreate,
        ProductVideo $productVideoEntity,
    ): void {
        foreach ($videoDataToCreate->videoTokenDescriptions as $descriptionLocale => $descriptionValue) {
            $productVideoTranslation = new ProductVideoTranslations();
            $productVideoTranslation->setLocale($descriptionLocale);
            $productVideoTranslation->setDescription($descriptionValue);
            $productVideoTranslation->setProductVideo($productVideoEntity);
            $this->em->persist($productVideoTranslation);
        }
    }

    protected function cleanProductVideoTranslationsForProductVideo(ProductVideo $productVideo): void
    {
        $productVideoTranslations = $this->productVideoTranslationsRepository->findByProductVideoId($productVideo->getId());

        foreach ($productVideoTranslations as $productVideoTranslation) {
            $this->em->remove($productVideoTranslation);
        }
    }
}
