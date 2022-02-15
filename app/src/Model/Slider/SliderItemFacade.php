<?php

declare(strict_types=1);

namespace App\Model\Slider;

use Shopsys\FrameworkBundle\Model\Slider\SliderItemData;
use Shopsys\FrameworkBundle\Model\Slider\SliderItemFacade as BaseSliderItemFacade;

/**
 * @property \App\Model\Slider\SliderItemRepository $sliderItemRepository
 * @property \App\Component\Image\ImageFacade $imageFacade
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 * @method \App\Model\Slider\SliderItem getById(int $sliderItemId)
 * @method \App\Model\Slider\SliderItem[] getAllVisibleOnCurrentDomain()
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Model\Slider\SliderItemRepository $sliderItemRepository, \App\Component\Image\ImageFacade $imageFacade, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Model\Slider\SliderItemFactoryInterface $sliderItemFactory)
 */
class SliderItemFacade extends BaseSliderItemFacade
{
    public const IMAGE_TYPE_MOBILE = 'mobile';
    public const IMAGE_TYPE_WEB = 'web';

    /**
     * @param \App\Model\Slider\SliderItemData $sliderItemData
     * @return \App\Model\Slider\SliderItem
     */
    public function create(SliderItemData $sliderItemData)
    {
        /** @var \App\Model\Slider\SliderItem $sliderItem */
        $sliderItem = $this->sliderItemFactory->create($sliderItemData);

        $this->em->persist($sliderItem);
        $this->em->flush();
        $this->imageFacade->manageImages($sliderItem, $sliderItemData->image, self::IMAGE_TYPE_WEB);
        $this->imageFacade->manageImages($sliderItem, $sliderItemData->mobileImage, self::IMAGE_TYPE_MOBILE);

        return $sliderItem;
    }

    /**
     * @param int $sliderItemId
     * @param \App\Model\Slider\SliderItemData $sliderItemData
     * @return \App\Model\Slider\SliderItem
     */
    public function edit($sliderItemId, SliderItemData $sliderItemData)
    {
        /** @var \App\Model\Slider\SliderItem $sliderItem */
        $sliderItem = $this->sliderItemRepository->getById($sliderItemId);
        $sliderItem->edit($sliderItemData);
        $this->em->flush();
        $this->imageFacade->manageImages($sliderItem, $sliderItemData->image, self::IMAGE_TYPE_WEB);
        $this->imageFacade->manageImages($sliderItem, $sliderItemData->mobileImage, self::IMAGE_TYPE_MOBILE);

        return $sliderItem;
    }

    /**
     * @param int $sliderItemId
     */
    public function delete($sliderItemId)
    {
        $sliderItem = $this->sliderItemRepository->getById($sliderItemId);

        $this->em->remove($sliderItem);
        $this->em->flush();
    }
}
