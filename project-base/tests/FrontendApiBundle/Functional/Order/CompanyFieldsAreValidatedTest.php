<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

class CompanyFieldsAreValidatedTest extends AbstractOrderTestCase
{
    public function testValidationErrorWhenCompanyBehalfIsTrueAndFieldsAreMissing(): void
    {
        $expectedValidations = [
            'input.companyName' => [
                0 => [
                    'message' => 'Please enter company name',
                    'code' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3',
                ],
            ],
            'input.companyNumber' => [
                0 => [
                    'message' => 'Please enter identification number',
                    'code' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3',
                ],
            ],
        ];

        $orderMutation = $this->getOrderMutation(__DIR__ . '/Resources/companyFieldsAreValidated.graphql');

        $responseData = $this->getResponseContentForQuery($orderMutation);

        $this->assertEquals($expectedValidations, $responseData['errors'][0]['extensions']['validation']);
    }
}
