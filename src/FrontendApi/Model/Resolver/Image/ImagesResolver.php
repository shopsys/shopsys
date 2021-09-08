<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Image;

use App\Model\Category\Category;
use App\Model\CategorySeo\ReadyCategorySeoMix;
use App\Model\NotificationBar\NotificationBar;
use App\Model\Slider\SliderItem;
use InvalidArgumentException;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrontendApiBundle\Model\Resolver\Image\ImagesResolver as BaseImagesResolver;

/**
 * @property \App\Component\Image\ImageFacade $imageFacade
 * @method __construct(\App\Component\Image\ImageFacade $imageFacade, \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig $imageConfig, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrontendApiBundle\Component\Image\ImageFacade|null $frontendApiImageFacade = null)
 * @method array resolveByProduct(\App\Model\Product\Product|array $data, string|null $type, string|null $size)
 * @method array resolveByCategory(\App\Model\Category\Category $category, string|null $type, string|null $size)
 * @method array resolveByPayment(\App\Model\Payment\Payment $payment, string|null $type, string|null $size)
 * @method array resolveByTransport(\App\Model\Transport\Transport $transport, string|null $type, string|null $size)
 * @method array resolveByBrand(\App\Model\Product\Brand\Brand $brand, string|null $type, string|null $size)
 * @method array resolveByAdvert(\App\Model\Advert\Advert $advert, string|null $type, string|null $size)
 * @method array getResolvedImage(\App\Component\Image\Image $image, \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig $sizeConfig)
 * @method \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[] getSizeConfigsForAdvert(\App\Model\Advert\Advert $advert, string|null $type, string|null $size)
 */
class ImagesResolver extends BaseImagesResolver
{
    protected const IMAGE_ENTITY_SLIDER_ITEM = 'sliderItem';
    protected const IMAGE_ENTITY_NOTIFICATION_BAR = 'notificationBar';

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
     * @param \App\Model\NotificationBar\NotificationBar $notificationBar
     * @param string|null $type
     * @param string|null $size
     * @return array
     */
    public function resolveByNotificationBar(NotificationBar $notificationBar, ?string $type, ?string $size): array
    {
        return $this->resolveByEntityId($notificationBar->getId(), static::IMAGE_ENTITY_NOTIFICATION_BAR, $type, $size);
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

    /**
     * @param \App\Model\Category\Category|\App\Model\CategorySeo\ReadyCategorySeoMix $categoryOrReadyCategorySeoMix
     * @param string|null $type
     * @param string|null $size
     * @return array
     */
    public function resolveByCategoryOrReadyCategorySeoMix($categoryOrReadyCategorySeoMix, ?string $type, ?string $size): array
    {
        if ($categoryOrReadyCategorySeoMix instanceof Category) {
            $categoryId = $categoryOrReadyCategorySeoMix->getId();
        } elseif ($categoryOrReadyCategorySeoMix instanceof ReadyCategorySeoMix) {
            $categoryId = $categoryOrReadyCategorySeoMix->getCategory()->getId();
        } else {
            throw new InvalidArgumentException(
                sprintf(
                    'The "$categoryOrReadyCategorySeoMix" argument must be an instance of "%s" or "%s".',
                    Category::class,
                    ReadyCategorySeoMix::class
                ),
            );
        }

        return $this->resolveByEntityId($categoryId, static::IMAGE_ENTITY_CATEGORY, $type, $size);
    }
}
