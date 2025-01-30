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

    /**
     * @param \Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideoRepository $productVideoRepository
     * @param \Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideoTranslationsRepository $productVideoTranslationsRepository
     * @param \Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideoFactory $productVideoFactory
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly ProductVideoRepository $productVideoRepository,
        protected readonly ProductVideoTranslationsRepository $productVideoTranslationsRepository,
        protected readonly ProductVideoFactory $productVideoFactory,
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideoData[] $productVideoDataList
     */
    public function saveProductVideosToProduct(Product $product, array $productVideoDataList): void
    {
        $productVideos = $this->productVideoRepository->findByProductId($product->getId());

        $videoDataListToUpdate = array_filter($productVideoDataList, function (ProductVideoData $productVideoData) {
            return (bool)$productVideoData->id;
        });

        $videoDataListToCreate = array_filter($productVideoDataList, function (ProductVideoData $productVideoData) {
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
            $this->em->remove($productVideoToRemove);
        }

        foreach ($videoDataListToCreate as $videoDataToCreate) {
            $productVideoEntity = $this->productVideoFactory->create($videoDataToCreate);
            $productVideoEntity->setProduct($product);
            $productVideoEntity->setVideoToken(str_replace(self::YOUTUBE_LINKS_ARRAY, '', $videoDataToCreate->videoToken));
            $this->em->persist($productVideoEntity);

            $this->persistVideoTranslations($videoDataToCreate, $productVideoEntity);
        }
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideoData $videoDataToCreate
     * @param \Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideo $productVideoEntity
     */
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideo $productVideo
     */
    protected function cleanProductVideoTranslationsForProductVideo(ProductVideo $productVideo): void
    {
        $productVideoTranslations = $this->productVideoTranslationsRepository->findByProductVideoId($productVideo->getId());

        foreach ($productVideoTranslations as $productVideoTranslation) {
            $this->em->remove($productVideoTranslation);
        }
    }
}
