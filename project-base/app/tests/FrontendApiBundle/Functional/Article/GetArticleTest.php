<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Article;

use App\DataFixtures\Demo\ArticleDataFixture;
use App\Model\Article\Article;
use App\Model\Article\ArticleFacade;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\String\TransformString;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class GetArticleTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private ArticleFacade $articleFacade;

    /**
     * @inject
     */
    protected UrlGeneratorInterface $urlGenerator;

    public function testGetArticle(): void
    {
        foreach ($this->getArticleDataProvider() as $dataSet) {
            [$uuid, $expectedArticleData] = $dataSet;

            $graphQlType = 'article';
            $response = $this->getResponseContentForQuery($this->getArticleQuery($uuid));
            $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
            $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

            $this->assertArrayHasKey('name', $responseData);
            $this->assertSame($expectedArticleData['name'], $responseData['name']);

            $this->assertArrayHasKey('placement', $responseData);
            $this->assertSame($expectedArticleData['placement'], $responseData['placement']);

            $this->assertArrayHasKey('external', $responseData);
            $this->assertSame($expectedArticleData['external'], $responseData['external']);
        }
    }

    public function testGetArticleReturnsError(): void
    {
        $wrongUuid = '123e4567-e89b-12d3-a456-426614174000';
        $expectedErrorMessage = 'Article with UUID \'' . $wrongUuid . '\' not found.';

        $response = $this->getResponseContentForQuery($this->getArticleQuery($wrongUuid));
        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        $this->assertArrayHasKey(0, $errors);
        $this->assertArrayHasKey('message', $errors[0]);
        $this->assertSame($expectedErrorMessage, $errors[0]['message']);
    }

    public function testGetSpecialArticle(): void
    {
        foreach ($this->getSpecialArticleDataProvider() as $dataSet) {
            [$graphQlType, $expectedData] = $dataSet;

            $response = $this->getResponseContentForQuery($this->getSpecialArticleQuery($graphQlType));
            $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
            $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

            $this->assertArrayHasKey('uuid', $responseData);
            $this->assertTrue(Uuid::isValid($responseData['uuid']));

            $this->assertKeysAreSameAsExpected(
                [
                    'name',
                    'slug',
                    'placement',
                    'text',
                    'seoH1',
                    'seoTitle',
                    'seoMetaDescription',
                    'breadcrumb',
                ],
                $responseData,
                $expectedData,
            );
        }
    }

    /**
     * @param mixed[] $keys
     * @param mixed[] $actual
     * @param mixed[] $expected
     */
    private function assertKeysAreSameAsExpected(array $keys, array $actual, array $expected): void
    {
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $actual);
            $this->assertSame($expected[$key], $actual[$key]);
        }
    }

    /**
     * @return mixed[]
     */
    private function getArticleDataProvider(): array
    {
        $data = [];
        $articleIds = [1, 2, 3];

        foreach ($articleIds as $articleId) {
            $article = $this->articleFacade->getById($articleId);
            $data[] = [
                $article->getUuid(),
                [
                    'name' => $article->getName(),
                    'placement' => $article->getPlacement(),
                    'external' => $article->isExternal(),
                ],
            ];
        }

        return $data;
    }

    /**
     * @return array<'breadcrumb'|'name'|'placement'|'seoH1'|'seoMetaDescription'|'seoTitle'|'slug'|'text', string|array<int, array<'name'|'slug', string>>|null>[]|'termsAndConditionsArticle'[][]|array<'breadcrumb'|'name'|'placement'|'seoH1'|'seoMetaDescription'|'seoTitle'|'slug'|'text', string|array<int, array<'name'|'slug', string>>|null>[]|'cookiesArticle'[]|'privacyPolicyArticle'[][]
     */
    private function getSpecialArticleDataProvider(): array
    {
        /** @var \App\Model\Article\Article $termsAndConditionsArticle */
        $termsAndConditionsArticle = $this->getReferenceForDomain(ArticleDataFixture::ARTICLE_TERMS_AND_CONDITIONS, 1);
        /** @var \App\Model\Article\Article $privacyPolicyArticle */
        $privacyPolicyArticle = $this->getReferenceForDomain(ArticleDataFixture::ARTICLE_PRIVACY_POLICY, 1);
        /** @var \App\Model\Article\Article $cookiesArticle */
        $cookiesArticle = $this->getReferenceForDomain(ArticleDataFixture::ARTICLE_COOKIES, 1);

        $firstDomainLocale = $this->getLocaleForFirstDomain();

        return [
            [
                'termsAndConditionsArticle',
                [
                    'name' => t('Terms and conditions of department stores', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'slug' => '/' . TransformString::stringToFriendlyUrlSlug(t('Terms and conditions of department stores', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)),
                    'placement' => Article::PLACEMENT_FOOTER_4,
                    'text' => t(
                        '<div class="gjs-text-ckeditor">Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</div>',
                        [],
                        Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                        $firstDomainLocale,
                    ),
                    'seoH1' => null,
                    'seoTitle' => null,
                    'seoMetaDescription' => null,
                    'breadcrumb' => [
                        [
                            'name' => t('Terms and conditions of department stores', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                            'slug' => $this->urlGenerator->generate('front_article_detail', ['id' => $termsAndConditionsArticle->getId()]),
                        ],
                    ],
                ],
            ],
            [
                'privacyPolicyArticle',
                [
                    'name' => t('Privacy policy', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'slug' => '/' . TransformString::stringToFriendlyUrlSlug(t('Privacy policy', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)),
                    'placement' => Article::PLACEMENT_NONE,
                    'text' => t(
                        '<div class="gjs-text-ckeditor">Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</div>',
                        [],
                        Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                        $firstDomainLocale,
                    ),
                    'seoH1' => null,
                    'seoTitle' => null,
                    'seoMetaDescription' => null,
                    'breadcrumb' => [
                        [
                            'name' => t('Privacy policy', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                            'slug' => $this->urlGenerator->generate('front_article_detail', ['id' => $privacyPolicyArticle->getId()]),
                        ],
                    ],
                ],
            ],
            [
                'cookiesArticle',
                [
                    'name' => t('Information about cookies', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'slug' => '/' . TransformString::stringToFriendlyUrlSlug(t('Information about cookies', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)),
                    'placement' => Article::PLACEMENT_NONE,
                    'text' => t(
                        '<div class="gjs-text-ckeditor">Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</div>',
                        [],
                        Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                        $firstDomainLocale,
                    ),
                    'seoH1' => null,
                    'seoTitle' => null,
                    'seoMetaDescription' => null,
                    'breadcrumb' => [
                        [
                            'name' => t('Information about cookies', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                            'slug' => $this->urlGenerator->generate('front_article_detail', ['id' => $cookiesArticle->getId()]),
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param string $uuid
     * @return string
     */
    private function getArticleQuery(string $uuid): string
    {
        return '
            {
                article (uuid:"' . $uuid . '") {
                    name
                    placement
                    external
                }
            }
        ';
    }

    /**
     * @param string $specialArticle
     * @return string
     */
    private function getSpecialArticleQuery(string $specialArticle): string
    {
        return '
            {
                ' . $specialArticle . ' {
                    uuid
                    name
                    slug
                    placement
                    text
                    seoH1
                    seoTitle
                    seoMetaDescription
                    breadcrumb {
                        name
                        slug
                    }
                }
            }
        ';
    }
}
