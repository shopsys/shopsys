<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Payment\GoPay;

use App\DataFixtures\Demo\GoPayDataFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class GoPayQueryTest extends GraphQlTestCase
{
    public function testGoPaySwiftsQuery(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../graphql/GoPaySwiftsQuery.graphql', [
            'currencyCode' => $this->currencyFacade->getDomainDefaultCurrencyByDomainId(Domain::FIRST_DOMAIN_ID)->getCode(),
        ]);

        $locale = $this->getLocaleForFirstDomain();
        $data = $this->getResponseDataForGraphQlType($response, 'GoPaySwifts');
        $expected = [
            [
                'swift' => sprintf(GoPayDataFixture::AIRBANK_SWIFT_PATTERN, $locale),
                'name' => 'Airbank',
                'imageLargeUrl' => 'airbank large image Url',
                'imageNormalUrl' => 'airbank image Url',
                'isOnline' => true,
            ],
            [
                'swift' => sprintf(GoPayDataFixture::FIO_SWIFT_PATTERN, $locale),
                'name' => 'FIO bank',
                'imageLargeUrl' => 'FIO bank large image Url',
                'imageNormalUrl' => 'FIO bank image Url',
                'isOnline' => true,
            ],
        ];

        $this->assertSame($expected, $data);
    }
}
