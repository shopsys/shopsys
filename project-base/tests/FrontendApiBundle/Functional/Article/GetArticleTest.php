<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Article;

use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class GetArticleTest extends GraphQlTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Article\ArticleFacade
     * @inject
     */
    private $articleFacade;

    public function testGetArticle(): void
    {
        foreach ($this->getArticleDataProvider() as $dataSet) {
            list($uuid, $expectedArticleData) = $dataSet;

            $graphQlType = 'article';
            $response = $this->getResponseContentForQuery($this->getArticleQuery($uuid));
            $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
            $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

            $this->assertArrayHasKey('name', $responseData);
            $this->assertSame($expectedArticleData['name'], $responseData['name']);

            $this->assertArrayHasKey('placement', $responseData);
            $this->assertSame($expectedArticleData['placement'], $responseData['placement']);
        }
    }

    public function testGetArticleReturnsError(): void
    {
        $article = $this->getArticleOnDifferentDomain();
        $expectedErrorMessage = 'Article with UUID \'' . $article->getUuid() . '\' not found.';

        $response = $this->getResponseContentForQuery($this->getArticleQuery($article->getUuid()));
        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        $this->assertArrayHasKey(0, $errors);
        $this->assertArrayHasKey('message', $errors[0]);
        $this->assertSame($expectedErrorMessage, $errors[0]['message']);
    }

    /**
     * @param string $graphQlType
     * @param array $expectedData
     * @dataProvider getSpecialArticleDataProvider
     */
    public function testGetSpecialArticle(string $graphQlType, array $expectedData): void
    {
        $response = $this->getResponseContentForQuery($this->getSpecialArticleQuery($graphQlType));
        $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

        $this->assertArrayHasKey('uuid', $responseData);
        $this->assertTrue(Uuid::isValid($responseData['uuid']));

        $this->assertKeysAreSameAsExpected(
            [
                'name',
                'placement',
                'text',
                'seoH1',
                'seoTitle',
                'seoMetaDescription',
            ],
            $responseData,
            $expectedData
        );
    }

    /**
     * @param array $keys
     * @param array $actual
     * @param array $expected
     */
    private function assertKeysAreSameAsExpected(array $keys, array $actual, array $expected): void
    {
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $actual);
            $this->assertSame($expected[$key], $actual[$key]);
        }
    }

    /**
     * @return array
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
                ],
            ];
        }
        return $data;
    }

    /**
     * @return array
     */
    public function getSpecialArticleDataProvider(): array
    {
        return [
            [
                'termsAndConditionsArticle',
                [
                    'name' => 'Terms and conditions',
                    'placement' => Article::PLACEMENT_FOOTER,
                    'text' => 'Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.',
                    'seoH1' => null,
                    'seoTitle' => null,
                    'seoMetaDescription' => null,
                ],
            ],
            [
                'privacyPolicyArticle',
                [
                    'name' => 'Privacy policy',
                    'placement' => Article::PLACEMENT_NONE,
                    'text' => 'Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.',
                    'seoH1' => null,
                    'seoTitle' => null,
                    'seoMetaDescription' => null,
                ],
            ],
            [
                'cookiesArticle',
                [
                    'name' => 'Information about cookies',
                    'placement' => Article::PLACEMENT_NONE,
                    'text' => 'Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.',
                    'seoH1' => null,
                    'seoTitle' => null,
                    'seoMetaDescription' => null,
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
                    placement
                    text
                    seoH1
                    seoTitle
                    seoMetaDescription
                }
            }
        ';
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    private function getArticleOnDifferentDomain(): Article
    {
        return $this->articleFacade->getById(6);
    }
}
