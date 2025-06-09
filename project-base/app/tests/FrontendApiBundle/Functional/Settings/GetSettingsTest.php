<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Settings;

use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class GetSettingsTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private readonly SeoSettingFacade $seoSettingFacade;

    /**
     * @inject
     */
    private TransformStringHelper $transformStringHelper;

    /**
     * @param string|null $robotsTxtContent
     * @param string|null $robotsTxtData
     */
    #[DataProvider('dataProvider')]
    public function testGetSettings(?string $robotsTxtContent, ?string $robotsTxtData): void
    {
        $this->seoSettingFacade->setRobotsTxtContent($robotsTxtContent, $this->domain->getId());
        $expectedSettingsData = $this->getExpectedSettings($robotsTxtData);

        $graphQlType = 'settings';
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/SettingsQuery.graphql');
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

        self::assertSame($expectedSettingsData, $responseData);
    }

    /**
     * @return array
     */
    public static function dataProvider(): array
    {
        return [
            [
                null,
                null,
            ],
            [
                'Disallow: /private',
                'Disallow: /private',
            ],
            [
                sprintf('Disallow: /private-one%sDisallow: /private-two', PHP_EOL),
                <<<CONTENT
Disallow: /private-one
Disallow: /private-two
CONTENT,
            ],
        ];
    }

    /**
     * @param string|null $data
     * @return array
     */
    private function getExpectedSettings(?string $data): array
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();

        return [
            'seo' => [
                'robotsTxtContent' => $data,
            ],
            'termsAndConditionsArticleUrl' => '/' . $this->transformStringHelper->stringToFriendlyUrlSlug(t('Terms and conditions of department stores', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)),
            'privacyPolicyArticleUrl' => '/' . $this->transformStringHelper->stringToFriendlyUrlSlug(t('Privacy policy', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)),
            'userConsentPolicyArticleUrl' => '/' . $this->transformStringHelper->stringToFriendlyUrlSlug(t('User consent policy', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)),
        ];
    }
}
