<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class GetNullAsLastOrderTest extends GraphQlWithLoginTestCase
{
    public const string DEFAULT_USER_EMAIL = 'no-reply.3@shopsys.com';
    public const string DEFAULT_USER_PASSWORD = 'no-reply.3';

    public function testLastOrderOfUser(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/LastOrderQuery.graphql');
        $lastOrderData = $response['data']['lastOrder'];

        self::assertNull($lastOrderData);
    }
}
