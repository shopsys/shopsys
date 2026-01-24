<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Slider;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\Router\UrlNormalizer;

class SliderItemFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly SliderItemRepository $sliderItemRepository,
        protected readonly ImageFacade $imageFacade,
        protected readonly Domain $domain,
        protected readonly SliderItemFactory $sliderItemFactory,
        protected readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
    ) {
    }

    public function getById(int $sliderItemId): SliderItem
    {
        return $this->sliderItemRepository->getById($sliderItemId);
    }

    public function create(SliderItemData $sliderItemData): SliderItem
    {
        $this->fixUrlInSliderItemData($sliderItemData);

        $sliderItem = $this->sliderItemFactory->create($sliderItemData);
        $this->setSliderItemRouteName($sliderItem);

        $this->em->persist($sliderItem);
        $this->em->flush();
        $this->imageFacade->manageImages($sliderItem, $sliderItemData->image);

        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::SLIDER_ITEMS_QUERY_KEY_PART);

        return $sliderItem;
    }

    public function edit(
        int $sliderItemId,
        SliderItemData $sliderItemData,
    ): SliderItem {
        $sliderItem = $this->sliderItemRepository->getById($sliderItemId);
        $this->fixUrlInSliderItemData($sliderItemData);

        $sliderItem->edit($sliderItemData);
        $this->setSliderItemRouteName($sliderItem);

        $this->em->flush();
        $this->imageFacade->manageImages($sliderItem, $sliderItemData->image);

        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::SLIDER_ITEMS_QUERY_KEY_PART);

        return $sliderItem;
    }

    public function delete(int $sliderItemId): void
    {
        $sliderItem = $this->sliderItemRepository->getById($sliderItemId);

        $this->em->remove($sliderItem);
        $this->em->flush();

        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::SLIDER_ITEMS_QUERY_KEY_PART);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Slider\SliderItem[]
     */
    public function getAllVisibleOnCurrentDomain(): array
    {
        return $this->sliderItemRepository->getAllVisibleByDomainId($this->domain->getId());
    }

    protected function fixUrlInSliderItemData(SliderItemData $sliderItemData): void
    {
        $domainConfig = $this->domain->getDomainConfigById($sliderItemData->domainId);
        $sliderItemData->link = UrlNormalizer::normalizeUrl($sliderItemData->link, $domainConfig);
    }

    protected function setSliderItemRouteName(SliderItem $sliderItem): void
    {
        $friendlyUrl = $this->friendlyUrlFacade->findByDomainIdAndSlug($sliderItem->getDomainId(), trim($sliderItem->getLink(), '/'));
        $sliderItem->setRouteName($friendlyUrl?->getRouteName());
    }
}
