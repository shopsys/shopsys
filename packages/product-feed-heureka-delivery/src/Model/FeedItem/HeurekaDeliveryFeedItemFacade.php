<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaDeliveryBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;

class HeurekaDeliveryFeedItemFacade
{
    public function __construct(
        protected readonly HeurekaDeliveryDataRepository $heurekaDeliveryDataRepository,
        protected readonly HeurekaDeliveryFeedItemFactory $feedItemFactory,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
    ) {
    }

    /**
     * @return \Shopsys\ProductFeed\HeurekaDeliveryBundle\Model\FeedItem\HeurekaDeliveryFeedItem[]|iterable
     */
    public function getItems(DomainConfig $domainConfig, ?int $lastSeekId, int $maxResults): iterable
    {
        $pricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainConfig->getId());
        $dataRows = $this->heurekaDeliveryDataRepository->getDataRows(
            $domainConfig,
            $pricingGroup,
            $lastSeekId,
            $maxResults,
        );

        foreach ($dataRows as $dataRow) {
            yield $this->feedItemFactory->create($dataRow);
        }
    }
}
