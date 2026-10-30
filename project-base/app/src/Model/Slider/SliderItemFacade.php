<?php

declare(strict_types=1);

namespace App\Model\Slider;

use Shopsys\FrameworkBundle\Model\Slider\SliderItemFacade as BaseSliderItemFacade;

/**
 * @property \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 * @method \App\Model\Slider\SliderItem getById(int $sliderItemId)
 * @method \App\Model\Slider\SliderItem create(\App\Model\Slider\SliderItemData $sliderItemData)
 * @method \App\Model\Slider\SliderItem edit(int $sliderItemId, \App\Model\Slider\SliderItemData $sliderItemData)
 * @method \App\Model\Slider\SliderItem[] getAllVisibleOnCurrentDomain()
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \Shopsys\FrameworkBundle\Model\Slider\SliderItemRepository $sliderItemRepository, \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Model\Slider\SliderItemFactory $sliderItemFactory, \Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade $cleanStorefrontCacheFacade, \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade)
 * @method void fixUrlInSliderItemData(\App\Model\Slider\SliderItemData $sliderItemData)
 * @method void setSliderItemRouteName(\App\Model\Slider\SliderItem $sliderItem)
 */
class SliderItemFacade extends BaseSliderItemFacade
{
}
