<?php

declare(strict_types=1);

namespace App\Model\Slider;

use App\Twig\Cache\TwigCacheFacade;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Model\Slider\SliderItemData;
use Shopsys\FrameworkBundle\Model\Slider\SliderItemFacade as BaseSliderItemFacade;
use Shopsys\FrameworkBundle\Model\Slider\SliderItemFactoryInterface;
use Shopsys\FrameworkBundle\Model\Slider\SliderItemRepository;

/**
 * @property \App\Model\Slider\SliderItemRepository $sliderItemRepository
 * @property \App\Component\Image\ImageFacade $imageFacade
 * @property \App\Component\Domain\Domain $domain
 * @method \App\Model\Slider\SliderItem getById(int $sliderItemId)
 * @method \App\Model\Slider\SliderItem[] getAllVisibleOnCurrentDomain()
 */
class SliderItemFacade extends BaseSliderItemFacade
{
    public const IMAGE_TYPE_MOBILE = 'mobile';
    public const IMAGE_TYPE_WEB = 'web';

    /**
     * @var \App\Twig\Cache\TwigCacheFacade
     */
    private $twigCacheFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Slider\SliderItemRepository $sliderItemRepository
     * @param \App\Component\Image\ImageFacade $imageFacade
     * @param \App\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Slider\SliderItemFactoryInterface $sliderItemFactory
     * @param \App\Twig\Cache\TwigCacheFacade $twigCacheFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        SliderItemRepository $sliderItemRepository,
        ImageFacade $imageFacade,
        Domain $domain,
        SliderItemFactoryInterface $sliderItemFactory,
        TwigCacheFacade $twigCacheFacade
    ) {
        parent::__construct($em, $sliderItemRepository, $imageFacade, $domain, $sliderItemFactory);
        $this->twigCacheFacade = $twigCacheFacade;
    }

    /**
     * @param \App\Model\Slider\SliderItemData $sliderItemData
     * @throws \App\Twig\Cache\Exception\InvalidCacheLifetimeException
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
        $this->twigCacheFacade->invalidateByKey($this->twigCacheFacade::SLIGHTLY_CHANGING_PARTS_ON_HOMEPAGE, $sliderItem->getDomainId());

        return $sliderItem;
    }

    /**
     * @param int $sliderItemId
     * @param \App\Model\Slider\SliderItemData $sliderItemData
     * @throws \App\Twig\Cache\Exception\InvalidCacheLifetimeException
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
        $this->twigCacheFacade->invalidateByKey($this->twigCacheFacade::SLIGHTLY_CHANGING_PARTS_ON_HOMEPAGE, $sliderItem->getDomainId());

        return $sliderItem;
    }

    /**
     * @param int $sliderItemId
     * @throws \App\Twig\Cache\Exception\InvalidCacheLifetimeException
     */
    public function delete($sliderItemId)
    {
        $sliderItem = $this->sliderItemRepository->getById($sliderItemId);

        $this->em->remove($sliderItem);
        $this->em->flush();
        $this->twigCacheFacade->invalidateByKey($this->twigCacheFacade::SLIGHTLY_CHANGING_PARTS_ON_HOMEPAGE, $sliderItem->getDomainId());
    }
}
