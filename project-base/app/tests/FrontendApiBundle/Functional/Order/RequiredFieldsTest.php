<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class RequiredFieldsTest extends GraphQlTestCase
{
    use OrderTestTrait;

    public function testCreateMinimalOrderMutation(): void
    {
        $expectedViolationMessages = [
            'Field OrderInput.firstName of required type String! was not provided.',
            'Field OrderInput.lastName of required type String! was not provided.',
            'Field OrderInput.telephone of required type String! was not provided.',
            'Field OrderInput.onCompanyBehalf of required type Boolean! was not provided.',
            'Field OrderInput.street of required type String! was not provided.',
            'Field OrderInput.city of required type String! was not provided.',
            'Field OrderInput.postcode of required type String! was not provided.',
            'Field OrderInput.country of required type String! was not provided.',
            'Field OrderInput.isDeliveryAddressDifferentFromBilling of required type Boolean! was not provided.',
        ];

        $orderMutation = $this->getOrderMutation(__DIR__ . '/graphql/requiredFields.graphql');

        $response = $this->getResponseContentForQuery($orderMutation);
        $this->assertResponseContainsArrayOfErrors($response);
        $errorsFromResponse = $this->getErrorsFromResponse($response);

        $this->assertCount(count($expectedViolationMessages), $errorsFromResponse);

        foreach ($errorsFromResponse as $key => $responseRow) {
            $this->assertArrayHasKey('message', $responseRow);
            $this->assertEquals($expectedViolationMessages[$key], $responseRow['message']);
        }
    }
}
