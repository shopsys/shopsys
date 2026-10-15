<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Article;

use App\DataFixtures\Demo\ArticleDataFixture;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class GetArticleTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private ArticleFacade $articleFacade;

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

    public function testGetArticleReturnsExportedContent(): void
    {
        $uuid = Uuid::uuid5(ArticleDataFixture::UUID_NAMESPACE, 'For press' . Domain::FIRST_DOMAIN_ID)->toString();

        $response = $this->getResponseContentForQuery('
            {
                article (uuid:"' . $uuid . '") {
                    ... on ArticleSite {
                        content
                    }
                }
            }
        ');
        $content = $this->getResponseDataForGraphQlType($response, 'article')['content'];

        $this->assertSame(1, $content['version']);
        $this->assertSame('web', $content['profile']);

        $blocks = $content['blocks'];
        $this->assertSame(['text', 'products', 'text', 'products'], array_slice(array_column($blocks, 'type'), 0, 4));
        $this->assertSame(['9177759', '5964035'], $blocks[1]['data']['catnums']);

        $columnsBlock = $blocks[5];
        $this->assertSame('columns', $columnsBlock['type']);

        $imageBlock = $columnsBlock['children'][0][0];
        $this->assertSame('image', $imageBlock['type']);
        $this->assertNotEmpty($imageBlock['data']['url']);
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
}
