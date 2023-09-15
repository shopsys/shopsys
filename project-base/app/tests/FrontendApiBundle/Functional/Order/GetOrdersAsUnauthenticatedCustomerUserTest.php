<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class GetOrdersAsUnauthenticatedCustomerUserTest extends GraphQlTestCase
{
    public function testGetAllCustomerUserOrders(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/getOrders.graphql');
        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        $this->assertArrayHasKey(0, $errors);
        $this->assertArrayHasKey('message', $errors[0]);
        $this->assertSame(
            'Token is not valid.',
            $errors[0]['message'],
        );
    }
}
