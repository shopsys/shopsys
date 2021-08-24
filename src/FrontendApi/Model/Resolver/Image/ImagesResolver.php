<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Image;

use App\Model\Slider\SliderItem;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrontendApiBundle\Model\Resolver\Image\ImagesResolver as BaseImagesResolver;

class ImagesResolver extends BaseImagesResolver
{
    protected const IMAGE_ENTITY_SLIDER_ITEM = 'sliderItem';

    /**
     * @param \App\Model\Slider\SliderItem $sliderItem
     * @param string|null $type
     * @param string|null $size
     * @return array
     */
    public function resolveBySliderItem(SliderItem $sliderItem, ?string $type, ?string $size): array
    {
        return $this->resolveByEntityId($sliderItem->getId(), static::IMAGE_ENTITY_SLIDER_ITEM, $type, $size);
    }

    /**
     * @param \App\Component\Image\Image[] $images
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[] $sizeConfigs
     * @return array
     */
    protected function getResolvedImages(array $images, array $sizeConfigs): array
    {
        $resolvedImages = [];

        foreach ($images as $image) {
            foreach ($sizeConfigs as $sizeConfig) {
                try {
                    $resolvedImages[] = $this->getResolvedImage($image, $sizeConfig);
                } catch (ImageNotFoundException $exception) {
                    continue;
                }
            }
        }

        return $resolvedImages;
    }
}
