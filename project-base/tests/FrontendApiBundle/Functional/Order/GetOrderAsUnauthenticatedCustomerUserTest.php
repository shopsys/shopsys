<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrontendApiBundle\Component\Price\MoneyFormatterHelper;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class GetOrderAsUnauthenticatedCustomerUserTest extends GraphQlTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderFacade
     * @inject
     */
    private OrderFacade $orderFacade;

    public function testGetOrder(): void
    {
        foreach ($this->getOrderDataProvider() as $dataSet) {
            [$urlHash, $expectedOrderData] = $dataSet;

            $graphQlType = 'order';
            $response = $this->getResponseContentForQuery($this->getOrderQuery($urlHash));
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
        foreach ($this->getIncorrectOrderDataProvider() as $dataSet) {
            [$urlHash, $expectedErrorMessage] = $dataSet;

            $response = $this->getResponseContentForQuery($this->getOrderQuery($urlHash));
            $this->assertResponseContainsArrayOfErrors($response);
            $errors = $this->getErrorsFromResponse($response);

            $this->assertArrayHasKey(0, $errors);
            $this->assertArrayHasKey('message', $errors[0]);
            $this->assertSame($expectedErrorMessage, $errors[0]['message']);
        }
    }

    /**
     * @return array
     */
    public function getOrderDataProvider(): array
    {
        $data = [];
        $orderIds = [7, 8, 9];

        foreach ($orderIds as $orderId) {
            $order = $this->orderFacade->getById($orderId);
            $data[] = [
                $order->getUrlHash(),
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
     * @return array
     */
    public function getIncorrectOrderDataProvider(): array
    {
        return [
            [
                null,
                'You need to be logged in or provide argument \'urlHash\'.',
            ],
            [
                'foo',
                'Order with urlHash "foo" was not found.',
            ],
        ];
    }

    /**
     * @param string|null $urlHash
     * @return string
     */
    private function getOrderQuery(?string $urlHash = null): string
    {
        if ($urlHash !== null) {
            $graphQlTypeWithFilters = 'order (urlHash:"' . $urlHash . '")';
        } else {
            $graphQlTypeWithFilters = 'order';
        }

        return '
            {
                ' . $graphQlTypeWithFilters . ' {
                    status
                    totalPrice {
                        priceWithVat
                    }
                }
            }
        ';
    }
}
