<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Payment\GoPay;

use App\DataFixtures\Demo\GoPayDataFixture;
use Shopsys\FrameworkBundle\Component\DataFixture\DomainsForDataFixtureProvider;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class GoPayQueryTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private DomainsForDataFixtureProvider $domainsForDataFixtureProvider;

    public function testGoPaySwiftsQuery(): void
    {
        $currencyCode = $this->currencyFacade->getDomainDefaultCurrencyByDomainId(Domain::FIRST_DOMAIN_ID)->getCode();
        $response = $this->getResponseContentForGql(__DIR__ . '/../graphql/GoPaySwiftsQuery.graphql', [
            'currencyCode' => $currencyCode,
        ]);

        $data = $this->getResponseDataForGraphQlType($response, 'GoPaySwifts');
        $expected = $this->getExpectedData($currencyCode);

        $this->assertArrayElements($expected, $data);
    }

    /**
     * @return array[]
     */
    private function getExpectedData(string $firstDomainCurrencyCode): array
    {
        $relevantLocales = [];

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $domainDefaultCurrencyCode = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainConfig->getId())->getCode();

            if ($domainDefaultCurrencyCode === $firstDomainCurrencyCode) {
                $relevantLocales[] = $domainConfig->getLocale();
            }
        }

        $expectedData = [];

        foreach (array_unique($relevantLocales) as $locale) {
            $expectedData[] = [
                'swift' => sprintf(GoPayDataFixture::AIRBANK_SWIFT_PATTERN, $locale),
                'name' => 'Airbank',
                'imageLargeUrl' => 'airbank large image Url',
                'imageNormalUrl' => 'airbank image Url',
                'isOnline' => true,
            ];
            $expectedData[] = [
                'swift' => sprintf(GoPayDataFixture::FIO_SWIFT_PATTERN, $locale),
                'name' => 'FIO bank',
                'imageLargeUrl' => 'FIO bank large image Url',
                'imageNormalUrl' => 'FIO bank image Url',
                'isOnline' => true,
            ];
        }

        return $expectedData;
    }
}
