<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

class DeliveryFieldsAreValidatedTest extends AbstractOrderTestCase
{
    public function testValidationErrorWhenCompanyBehalfIsTrueAndFieldsAreMissing(): void
    {
        $expectedValidations = [
            'input.deliveryFirstName' => [
                0 => [
                    'message' => 'Please enter first name of contact person',
                    'code' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3',
                ],
            ],
            'input.deliveryLastName' => [
                0 => [
                    'message' => 'Please enter last name of contact person',
                    'code' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3',
                ],
            ],
            'input.deliveryStreet' => [
                0 => [
                    'message' => 'Please enter street',
                    'code' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3',
                ],
            ],
            'input.deliveryCity' => [
                0 => [
                    'message' => 'Please enter city',
                    'code' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3',
                ],
            ],
            'input.deliveryPostcode' => [
                0 => [
                    'message' => 'Please enter zip code',
                    'code' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3',
                ],
            ],
            'input.deliveryCountry' => [
                0 => [
                    'message' => 'Please choose country',
                    'code' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3',
                ],
            ],
        ];

        $orderMutation = $this->getOrderMutation(__DIR__ . '/Resources/deliveryFieldsAreValidated.graphql');

        $responseData = $this->getResponseContentForQuery($orderMutation);

        $this->assertEquals($expectedValidations, $responseData['errors'][0]['extensions']['validation']);
    }
}
