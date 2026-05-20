<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Blog\Article;

use App\DataFixtures\Demo\BlogArticleDataFixture;
use Override;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class BlogArticleVisibilityTest extends GraphQlTestCase
{
    private BlogArticle $previewArticle;

    private BlogArticle $draftArticle;

    private BlogArticle $publishedFutureArticle;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->previewArticle = $this->getReference(BlogArticleDataFixture::BLOG_ARTICLE_PREVIEW, BlogArticle::class);
        $this->draftArticle = $this->getReference(BlogArticleDataFixture::BLOG_ARTICLE_DRAFT, BlogArticle::class);
        $this->publishedFutureArticle = $this->getReference(BlogArticleDataFixture::BLOG_ARTICLE_PUBLISHED_FUTURE, BlogArticle::class);
    }

    public function testPreviewArticleIsAccessibleByUuid(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/BlogArticleByUuidQuery.graphql', [
            'uuid' => $this->previewArticle->getUuid(),
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'blogArticle');

        $this->assertSame($this->previewArticle->getUuid(), $data['uuid']);
        $this->assertSame('preview', $data['status']);
    }

    public function testPreviewArticleIsNotInListing(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/BlogArticlesQuery.graphql');
        $data = $this->getResponseDataForGraphQlType($response, 'blogArticles');

        $uuids = array_map(
            static fn (array $edge) => $edge['node']['uuid'],
            $data['edges'],
        );

        $this->assertNotContains($this->previewArticle->getUuid(), $uuids);
    }

    public function testDraftArticleIsNotAccessibleByUuid(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/BlogArticleByUuidQuery.graphql', [
            'uuid' => $this->draftArticle->getUuid(),
        ]);

        $this->assertBlogArticleNotFoundError($response);
    }

    public function testPublishedArticleWithFutureDateIsNotInListing(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/BlogArticlesQuery.graphql');
        $data = $this->getResponseDataForGraphQlType($response, 'blogArticles');

        $uuids = array_map(
            static fn (array $edge) => $edge['node']['uuid'],
            $data['edges'],
        );

        $this->assertNotContains($this->publishedFutureArticle->getUuid(), $uuids);
    }

    public function testPublishedArticleWithFutureDateIsNotAccessibleByUuid(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/BlogArticleByUuidQuery.graphql', [
            'uuid' => $this->publishedFutureArticle->getUuid(),
        ]);

        $this->assertBlogArticleNotFoundError($response);
    }

    private function assertBlogArticleNotFoundError(array $response): void
    {
        $this->assertUserError($response, 'blog-article-not-found');
    }
}
