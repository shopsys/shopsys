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
            [$uuid, $orderNumber, $expectedOrderData] = $dataSet;

            $graphQlType = 'order';
            $responseByUuid = $this->getResponseContentForGql(__DIR__ . '/graphql/GetOrderQuery.graphql', [
                'uuid' => $uuid,
            ]);
            $this->assertResponseContainsArrayOfDataForGraphQlType($responseByUuid, $graphQlType);
            $responseData = $this->getResponseDataForGraphQlType($responseByUuid, $graphQlType);

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

            $responseByOrderNumber = $this->getResponseContentForGql(__DIR__ . '/graphql/GetOrderQuery.graphql', [
                'orderNumber' => $orderNumber,
            ]);
            $this->assertSame($responseByUuid, $responseByOrderNumber);
        }
    }

    public function testGetOrderByUuidReturnsError(): void
    {
        $order = $this->getOrderOfNotCurrentlyLoggedCustomerUser();
        $expectedErrorMessage = "Order with UUID 'e4002d79-0dba-4899-a51a-0a8feec3c2ce' not found.";

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/GetOrderQuery.graphql', [
            'uuid' => $order->getUuid(),
        ]);
        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        $this->assertArrayHasKey(0, $errors);
        $this->assertArrayHasKey('message', $errors[0]);
        $this->assertSame($expectedErrorMessage, $errors[0]['message']);
    }

    public function testGetOrderByOrderNumberReturnsError(): void
    {
        $order = $this->getOrderOfNotCurrentlyLoggedCustomerUser();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/GetOrderQuery.graphql', [
            'orderNumber' => $order->getNumber(),
        ]);
        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        $this->assertArrayHasKey(0, $errors);
        $this->assertArrayHasKey('message', $errors[0]);
        $this->assertMatchesRegularExpression("/Order with order number '\d+' not found./", $errors[0]['message']);
    }

    /**
     * @return array
     */
    private function getOrderDataForCurrentlyLoggedCustomerUserProvider(): array
    {
        $data = [];
        $orderIds = [1, 2, 3, 4];

        foreach ($orderIds as $orderId) {
            /** @var \App\Model\Order\Order $order */
            $order = $this->orderFacade->getById($orderId);
            $data[] = [
                $order->getUuid(),
                $order->getNumber(),
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
                ],
            ];
        }

        return $data;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    private function getOrderOfNotCurrentlyLoggedCustomerUser(): Order
    {
        return $this->orderFacade->getById(7);
    }
}
