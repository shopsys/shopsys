<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Payment\GoPay;

use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class GoPayQueryTest extends GraphQlTestCase
{
    public function testGoPaySwiftsQuery(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../graphql/GoPaySwiftsQuery.graphql', [
            'currencyCode' => 'CZK',
        ]);

        $data = $this->getResponseDataForGraphQlType($response, 'GoPaySwifts');
        $expected = [
            [
                'swift' => '123456XZY',
                'name' => 'Airbank',
                'imageLargeUrl' => 'airbank large image Url',
                'imageNormalUrl' => 'airbank image Url',
                'isOnline' => true,
            ],
            [
                'swift' => 'ABC123456',
                'name' => 'Aqua bank',
                'imageLargeUrl' => 'Aqua bank large image Url',
                'imageNormalUrl' => 'Aqua bank image Url',
                'isOnline' => true,
            ],
        ];

        $this->assertSame($expected, $data);
    }
}
