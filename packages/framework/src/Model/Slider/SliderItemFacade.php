<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Slider;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;

class SliderItemFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Slider\SliderItemRepository $sliderItemRepository
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Slider\SliderItemFactoryInterface $sliderItemFactory
     * @param \Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade $cleanStorefrontCacheFacade
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly SliderItemRepository $sliderItemRepository,
        protected readonly ImageFacade $imageFacade,
        protected readonly Domain $domain,
        protected readonly SliderItemFactoryInterface $sliderItemFactory,
        protected readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
    ) {
    }

    /**
     * @param int $sliderItemId
     * @return \Shopsys\FrameworkBundle\Model\Slider\SliderItem
     */
    public function getById($sliderItemId)
    {
        return $this->sliderItemRepository->getById($sliderItemId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Slider\SliderItemData $sliderItemData
     * @return \Shopsys\FrameworkBundle\Model\Slider\SliderItem
     */
    public function create(SliderItemData $sliderItemData)
    {
        $sliderItem = $this->sliderItemFactory->create($sliderItemData);

        $this->em->persist($sliderItem);
        $this->em->flush();
        $this->imageFacade->manageImages($sliderItem, $sliderItemData->image);

        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::SLIDER_ITEMS_QUERY_KEY_PART);

        return $sliderItem;
    }

    /**
     * @param int $sliderItemId
     * @param \Shopsys\FrameworkBundle\Model\Slider\SliderItemData $sliderItemData
     * @return \Shopsys\FrameworkBundle\Model\Slider\SliderItem
     */
    public function edit($sliderItemId, SliderItemData $sliderItemData)
    {
        $sliderItem = $this->sliderItemRepository->getById($sliderItemId);
        $sliderItem->edit($sliderItemData);

        $this->em->flush();
        $this->imageFacade->manageImages($sliderItem, $sliderItemData->image);

        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::SLIDER_ITEMS_QUERY_KEY_PART);

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

        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::SLIDER_ITEMS_QUERY_KEY_PART);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Slider\SliderItem[]
     */
    public function getAllVisibleOnCurrentDomain()
    {
        return $this->sliderItemRepository->getAllVisibleByDomainId($this->domain->getId());
    }
}
