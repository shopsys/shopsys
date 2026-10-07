<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Settings;

use App\DataFixtures\Demo\OrganizationDataFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class SeoSettingTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private MailSettingFacade $mailSettingFacade;

    public function testGetSeoSettings(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/SeoSettingsQuery.graphql');
        $data = $this->getResponseDataForGraphQlType($response, 'settings');

        $expectedTitleAddOn = t('| Demo eshop', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain());

        self::assertSame($expectedTitleAddOn, $data['seo']['titleAddOn']);
    }

    public function testOrganizationDemoDataAndLogoAreReturned(): void
    {
        $organization = $this->getOrganizationFromApi();

        self::assertSame(OrganizationDataFixture::ORGANIZATION_NAME, $organization['name']);
        self::assertSame(OrganizationDataFixture::ORGANIZATION_COMPANY_TAX_NUMBER, $organization['companyTaxNumber']);
        self::assertSame(OrganizationDataFixture::ORGANIZATION_CITY, $organization['city']);
        self::assertSame(
            [
                'url' => $this->getBaseUrlPath('/content-test/images/organization/810.png'),
                'name' => t('Shopsys logo', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
            ],
            $organization['logo'],
        );
        self::assertNotContains('', $organization['socialNetworkUrls']);
    }

    public function testOnlyFilledSocialNetworkUrlsAreReturned(): void
    {
        $this->mailSettingFacade->setFacebookUrl('https://www.facebook.com/shopsys', Domain::FIRST_DOMAIN_ID);
        $this->mailSettingFacade->setInstagramUrl(null, Domain::FIRST_DOMAIN_ID);
        $this->mailSettingFacade->setYoutubeUrl(null, Domain::FIRST_DOMAIN_ID);
        $this->mailSettingFacade->setLinkedInUrl(null, Domain::FIRST_DOMAIN_ID);
        $this->mailSettingFacade->setTiktokUrl(null, Domain::FIRST_DOMAIN_ID);

        $organization = $this->getOrganizationFromApi();

        self::assertSame(['https://www.facebook.com/shopsys'], $organization['socialNetworkUrls']);
    }

    /**
     * @return array{name: string|null, companyTaxNumber: string|null, city: string|null, logo: array{url: string, name: string|null}|null, socialNetworkUrls: string[]}
     */
    private function getOrganizationFromApi(): array
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/SeoOrganizationQuery.graphql');

        return $this->getResponseDataForGraphQlType($response, 'settings')['seo']['organization'];
    }
}
