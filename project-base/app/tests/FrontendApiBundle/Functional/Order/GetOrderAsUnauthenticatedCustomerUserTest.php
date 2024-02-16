<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrontendApiBundle\Component\Price\MoneyFormatterHelper;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class GetOrderAsUnauthenticatedCustomerUserTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private OrderFacade $orderFacade;

    public function testGetOrder(): void
    {
        foreach ($this->getOrderDataProvider() as $dataSet) {
            [$urlHash, $expectedOrderData] = $dataSet;

            $graphQlType = 'order';
            $response = $this->getResponseContentForGql(__DIR__ . '/graphql/GetOrderQuery.graphql', [
                'urlHash' => $urlHash,
            ]);
            $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
            $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

            $this->assertArrayHasKey('status', $responseData);
            $this->assertSame($expectedOrderData['status'], $responseData['status']);

            $this->assertArrayHasKey('totalPrice', $responseData);
            $this->assertArrayHasKey('priceWithVat', $responseData['totalPrice']);
            $this->assertSame($expectedOrderData['totalPriceWithVat'], $responseData['totalPrice']['priceWithVat']);

            $this->assertArrayHasKey('firstName', $responseData);
            $this->assertSame($expectedOrderData['firstName'], $responseData['firstName']);

            $this->assertArrayHasKey('lastName', $responseData);
            $this->assertSame($expectedOrderData['lastName'], $responseData['lastName']);

            $this->assertArrayHasKey('promoCode', $responseData);
            $this->assertSame($expectedOrderData['promoCode'], $responseData['promoCode']);

            $this->assertArrayHasKey('trackingNumber', $responseData);
            $this->assertSame($expectedOrderData['trackingNumber'], $responseData['trackingNumber']);

            $this->assertArrayHasKey('trackingUrl', $responseData);
            $this->assertSame($expectedOrderData['trackingUrl'], $responseData['trackingUrl']);

            $this->assertArrayHasKey('paymentTransactionsCount', $responseData);
            $this->assertSame($expectedOrderData['paymentTransactionsCount'], $responseData['paymentTransactionsCount']);

            $this->assertArrayHasKey('isPaid', $responseData);
            $this->assertSame($expectedOrderData['isPaid'], $responseData['isPaid']);
        }
    }

    public function testGetOrderReturnsError(): void
    {
        foreach ($this->getIncorrectOrderDataProvider() as $dataSet) {
            [$urlHash, $expectedErrorMessage] = $dataSet;

            $response = $this->getResponseContentForGql(__DIR__ . '/graphql/GetOrderQuery.graphql', [
                'urlHash' => $urlHash,
            ]);
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
        $orderIds = [7, 8, 9, 10];

        foreach ($orderIds as $orderId) {
            /** @var \App\Model\Order\Order $order */
            $order = $this->orderFacade->getById($orderId);
            $data[] = [
                $order->getUrlHash(),
                [
                    'status' => $order->getStatus()->getName(),
                    'totalPriceWithVat' => MoneyFormatterHelper::formatWithMaxFractionDigits(
                        $order->getTotalPriceWithVat(),
                    ),
                    'firstName' => $order->getFirstName(),
                    'lastName' => $order->getLastName(),
                    'promoCode' => $order->getGtmCoupon(),
                    'trackingNumber' => $order->getTrackingNumber(),
                    'trackingUrl' => $order->getTrackingUrl(),
                    'paymentTransactionsCount' => $order->getPaymentTransactionsCount(),
                    'isPaid' => $order->isPaid(),
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
                'Order not found',
            ],
        ];
    }
}
