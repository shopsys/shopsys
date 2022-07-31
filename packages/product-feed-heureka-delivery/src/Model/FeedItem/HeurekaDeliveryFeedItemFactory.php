<?php

namespace Shopsys\ProductFeed\HeurekaDeliveryBundle\Model\FeedItem;

class HeurekaDeliveryFeedItemFactory
{
    /**
     * @param array{id: int, stockQuantity: ?int} $dataRow
     * @return \Shopsys\ProductFeed\HeurekaDeliveryBundle\Model\FeedItem\HeurekaDeliveryFeedItem
     */
    public function create(array $dataRow): HeurekaDeliveryFeedItem
    {
        foreach (['id', 'stockQuantity'] as $requiredKey) {
            if (!array_key_exists($requiredKey, $dataRow)) {
                throw new HeurekaDeliveryDataMissingException($requiredKey);
            }
        }

        return new HeurekaDeliveryFeedItem($dataRow['id'], $dataRow['stockQuantity']);
    }
}
