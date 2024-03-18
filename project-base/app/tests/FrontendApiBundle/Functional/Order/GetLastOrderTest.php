<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\OrderDataFixture;
use App\Model\Order\Order;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class GetLastOrderTest extends GraphQlWithLoginTestCase
{
    public function testLastOrderOfUser(): void
    {
        $query = '
            {
                lastOrder {
                    number
                    deliveryStreet
                    deliveryCity
                }
            }
        ';

        $response = $this->getResponseContentForQuery($query);
        $data = $this->getResponseDataForGraphQlType($response, 'lastOrder');

        $expectedOrder = $this->getReference(OrderDataFixture::ORDER_PREFIX . '4', Order::class);

        self::assertEquals($expectedOrder->getNumber(), $data['number']);
        self::assertEquals($expectedOrder->getDeliveryStreet(), $data['deliveryStreet']);
        self::assertEquals($expectedOrder->getDeliveryCity(), $data['deliveryCity']);
    }
}
