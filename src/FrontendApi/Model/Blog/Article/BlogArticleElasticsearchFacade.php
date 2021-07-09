<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Blog\Article;

class BlogArticleElasticsearchFacade
{
    /**
     * @var \App\FrontendApi\Model\Blog\Article\BlogArticleElasticsearchRepository
     */
    private BlogArticleElasticsearchRepository $blogArticleElasticsearchRepository;

    /**
     * @param \App\FrontendApi\Model\Blog\Article\BlogArticleElasticsearchRepository $blogArticleElasticsearchRepository
     */
    public function __construct(BlogArticleElasticsearchRepository $blogArticleElasticsearchRepository)
    {
        $this->blogArticleElasticsearchRepository = $blogArticleElasticsearchRepository;
    }

    /**
     * @param string $uuid
     * @return array
     */
    public function getByUuid(string $uuid): array
    {
        return $this->blogArticleElasticsearchRepository->getByUuid($uuid);
    }
}
