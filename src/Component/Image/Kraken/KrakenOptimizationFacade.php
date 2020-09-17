<?php

declare(strict_types=1);

namespace App\Component\Image\Kraken;

use App\Component\Image\Image;
use App\Component\Image\ImageFacade;
use App\Component\Image\Processing\ImageGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Symfony\Bridge\Monolog\Logger;

class KrakenOptimizationFacade
{
    private const BATCH_SIZE = 20;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    private $logger;

    /**
     * @var \App\Component\Image\ImageFacade
     */
    private $imageFacade;

    /**
     * @var \App\Component\Image\Config\ImageConfig
     */
    private $imageConfig;

    /**
     * @var \App\Component\Image\Processing\ImageGenerator
     */
    private $imageGenerator;

    /**
     * @var \App\Component\Image\Kraken\KrakenConfig
     */
    private $krakenConfig;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Symfony\Bridge\Monolog\Logger $logger
     * @param \App\Component\Image\ImageFacade $imageFacade
     * @param \App\Component\Image\Config\ImageConfig $imageConfig
     * @param \App\Component\Image\Processing\ImageGenerator $imageGenerator
     * @param \App\Component\Image\Kraken\KrakenConfig $krakenConfig
     */
    public function __construct(
        EntityManagerInterface $em,
        Logger $logger,
        ImageFacade $imageFacade,
        ImageConfig $imageConfig,
        ImageGenerator $imageGenerator,
        KrakenConfig $krakenConfig
    ) {
        $this->em = $em;
        $this->logger = $logger;
        $this->imageFacade = $imageFacade;
        $this->imageConfig = $imageConfig;
        $this->imageGenerator = $imageGenerator;
        $this->krakenConfig = $krakenConfig;
    }

    /**
     * @return bool
     */
    public function runOptimization(): bool
    {
        if ($this->krakenConfig->isEnabled() === false) {
            $this->logger->addInfo('Kraken is disabled');
            return false;
        }

        /** @var \App\Component\Image\Image[] $images */
        $images = $this->imageFacade->findImagesForKrakenOptimization();

        if (count($images) === 0) {
            $this->logger->addInfo('No images for optimization');
            return false;
        }

        $processed = 0;
        foreach ($images as $image) {
            $entityConfig = $this->imageConfig->getEntityConfigByEntityName($image->getEntityName());
            $sizeConfigs = $entityConfig->getSizeConfigs();

            unset($sizeConfigs[ImageConfig::ORIGINAL_SIZE_NAME]);

            if ($this->optimizationImageForSizes($image, $sizeConfigs)) {
                $image->setProcessedByKraken(true);
                $this->em->persist($image);
                $processed++;
            }

            if ($processed === self::BATCH_SIZE) {
                $this->em->flush();

                return true;
            }
        }

        if (count($images) > 0) {
            $this->em->flush();
        }

        return false;
    }

    /**
     * @param \App\Component\Image\Image $image
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[] $sizeConfigs
     * @return bool
     */
    private function optimizationImageForSizes(Image $image, array $sizeConfigs): bool
    {
        try {
            $result = $this->imageGenerator->processImageSizesInKraken($image, $sizeConfigs);
            $this->logger->addInfo(sprintf('Optimize image for entity %s with id: %s', $image->getEntityName(), $image->getId()));
        } catch (ImageNotFoundException $exception) {
            $this->logger->addError(sprintf('Original Image %s, for entity %s with id: %s', $exception->getMessage(), $image->getEntityName(), $image->getId()));
            $result = false;
        }

        return $result;
    }
}
