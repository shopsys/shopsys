<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Article;

use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;
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
                    'external' => $article->isExternal(),
                ],
            ];
        }

        return $data;
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
}
