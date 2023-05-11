<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrontendApiBundle\Component\Price\MoneyFormatterHelper;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class GetOrderAsAuthenticatedCustomerUserTest extends GraphQlWithLoginTestCase
{
    /**
     * @inject
     */
    private OrderFacade $orderFacade;

    public function testGetOrder(): void
    {
        foreach ($this->getOrderDataForCurrentlyLoggedCustomerUserProvider() as $dataSet) {
            [$uuid, $expectedOrderData] = $dataSet;

            $graphQlType = 'order';
            $response = $this->getResponseContentForQuery($this->getOrderQuery($uuid));
            $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
            $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

            $this->assertArrayHasKey('status', $responseData);
            $this->assertSame($expectedOrderData['status'], $responseData['status']);

            $this->assertArrayHasKey('totalPrice', $responseData);
            $this->assertArrayHasKey('priceWithVat', $responseData['totalPrice']);
            $this->assertSame($expectedOrderData['totalPriceWithVat'], $responseData['totalPrice']['priceWithVat']);
        }
    }

    public function testGetOrderReturnsError(): void
    {
        $order = $this->getOrderOfNotCurrentlyLoggedCustomerUser();
        $expectedErrorMessage = 'Order with UUID \'' . $order->getUuid() . '\' not found.';

        $response = $this->getResponseContentForQuery($this->getOrderQuery($order->getUuid()));
        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        $this->assertArrayHasKey(0, $errors);
        $this->assertArrayHasKey('message', $errors[0]);
        $this->assertSame($expectedErrorMessage, $errors[0]['message']);
    }

    /**
     * @return array
     */
    private function getOrderDataForCurrentlyLoggedCustomerUserProvider(): array
    {
        $data = [];
        $orderIds = [1, 2, 3];

        foreach ($orderIds as $orderId) {
            $order = $this->orderFacade->getById($orderId);
            $data[] = [
                $order->getUuid(),
                [
                    'status' => $order->getStatus()->getName(),
                    'totalPriceWithVat' => MoneyFormatterHelper::formatWithMaxFractionDigits(
                        $order->getTotalPriceWithVat()
                    ),
                ],
            ];
        }

        return $data;
    }

    /**
     * @param string $uuid
     * @return string
     */
    private function getOrderQuery(string $uuid): string
    {
        return '
            {
                order (uuid:"' . $uuid . '") {
                    status
                    totalPrice {
                        priceWithVat
                    }
                }
            }
        ';
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    private function getOrderOfNotCurrentlyLoggedCustomerUser(): Order
    {
        return $this->orderFacade->getById(7);
    }
}
