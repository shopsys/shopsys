<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Image;

use GraphQL\Executor\Promise\Promise;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;

class OrderItemImagesQuery extends ImagesQuery
{
    public function mainImageByOrderItemPromiseQuery(OrderItem $orderItem, ?string $type): ?Promise
    {
        $imagedEntity = $this->getImagedEntity($orderItem);

        if ($imagedEntity === null) {
            return null;
        }

        return $this->mainImageByEntityPromiseQuery($imagedEntity, $type);
    }

    protected function getImagedEntity(OrderItem $orderItem): ?object
    {
        if ($orderItem->isTypeProduct()) {
            return $orderItem->getProduct();
        }

        if ($orderItem->isTypeAdditionalService()) {
            return $orderItem->getAdditionalService();
        }

        if ($orderItem->isTypeTransport()) {
            return $orderItem->getTransport();
        }

        if ($orderItem->isTypePayment()) {
            return $orderItem->getPayment();
        }

        return null;
    }
}
