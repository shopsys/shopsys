<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class GetNullAsLastOrderTest extends GraphQlWithLoginTestCase
{
    public const DEFAULT_USER_EMAIL = 'no-reply.3@shopsys.com';
    public const DEFAULT_USER_PASSWORD = 'no-reply.3';

    public function testLastOrderOfUser(): void
    {
        $query = '
            {
                lastOrder {
                    number
                }
            }
        ';

        $response = $this->getResponseContentForQuery($query);
        $lastOrderData = $response['data']['lastOrder'];

        self::assertNull($lastOrderData);
    }
}
