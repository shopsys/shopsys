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
                'swift' => 'AIRACSPP',
                'name' => 'Airbank',
                'imageLargeUrl' => 'airbank large image Url',
                'imageNormalUrl' => 'airbank image Url',
                'isOnline' => true,
            ],
            [
                'swift' => 'FIOBCSPP',
                'name' => 'FIO banka',
                'imageLargeUrl' => 'FIO bank large image Url',
                'imageNormalUrl' => 'FIO bank image Url',
                'isOnline' => true,
            ],
        ];

        $this->assertSame($expected, $data);
    }
}
