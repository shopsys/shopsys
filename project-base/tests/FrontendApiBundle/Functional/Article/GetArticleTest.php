<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Article;

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
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    private function getArticleOnDifferentDomain(): Article
    {
        return $this->articleFacade->getById(6);
    }
}
