<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

class RequiredFieldsTest extends AbstractOrderTestCase
{
    public function testCreateMinimalOrderMutation(): void
    {
        $expectedViolationMessages = [
            0 => 'Field OrderInput.firstName of required type String! was not provided.',
            1 => 'Field OrderInput.lastName of required type String! was not provided.',
            2 => 'Field OrderInput.email of required type String! was not provided.',
            3 => 'Field OrderInput.telephone of required type String! was not provided.',
            4 => 'Field OrderInput.onCompanyBehalf of required type Boolean! was not provided.',
            5 => 'Field OrderInput.street of required type String! was not provided.',
            6 => 'Field OrderInput.city of required type String! was not provided.',
            7 => 'Field OrderInput.postcode of required type String! was not provided.',
            8 => 'Field OrderInput.country of required type String! was not provided.',
            9 => 'Field OrderInput.differentDeliveryAddress of required type Boolean! was not provided.',
            10 => 'Field OrderInput.payment of required type PaymentInput! was not provided.',
            11 => 'Field OrderInput.transport of required type TransportInput! was not provided.',
            12 => 'Field OrderInput.products of required type [OrderProductInput!]! was not provided.',
        ];

        $orderMutation = $this->getOrderMutation(__DIR__ . '/Resources/requiredFields.graphql');

        $response = $this->getResponseContentForQuery($orderMutation);
        $this->assertResponseContainsArrayOfErrors($response);

        foreach ($this->getErrorsFromResponse($response) as $key => $responseRow) {
            $this->assertArrayHasKey('message', $responseRow);
            $this->assertEquals($expectedViolationMessages[$key], $responseRow['message']);
        }
    }
}
