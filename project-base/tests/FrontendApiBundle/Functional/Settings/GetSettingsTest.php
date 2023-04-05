<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Settings;

use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class GetSettingsTest extends GraphQlTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade
     * @inject
     */
    private readonly SeoSettingFacade $seoSettingFacade;

    /**
     * @dataProvider dataProvider
     * @param string|null $robotsContent
     * @param array $robotsData
     */
    public function testGetSettings(?string $robotsContent, array $robotsData): void
    {
        $this->seoSettingFacade->setRobotsContent($robotsContent, $this->domain->getId());
        $expectedSettingsData = $this->getExpectedSettings($robotsData);

        $graphQlType = 'settings';
        $response = $this->getResponseContentForQuery($this->getSettingsQuery());
        $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

        self::assertSame($expectedSettingsData, $responseData);
    }

    /**
     * @return array
     */
    public function dataProvider(): array
    {
        return [
            [
                null,
                [],
            ],
            [
                'Disallow: /private',
                ['Disallow: /private'],
            ],
            [
                sprintf('Disallow: /private-one%sDisallow: /private-two', PHP_EOL),
                ['Disallow: /private-one', 'Disallow: /private-two'],
            ],
        ];
    }

    /**
     * @return string
     */
    private function getSettingsQuery(): string
    {
        return '
            {
                settings {
                    seo {
                        robots
                    }
                }
            }
        ';
    }

    /**
     * @param array $data
     * @return array
     */
    private function getExpectedSettings(array $data): array
    {
        return [
            'seo' => [
                'robots' => $data,
            ],
        ];
    }
}
