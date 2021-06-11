<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Blog\Article;

class BlogArticleElasticFacade
{
    /**
     * @var \App\FrontendApi\Model\Blog\Article\BlogArticleElasticRepository
     */
    private BlogArticleElasticRepository $blogArticleElasticRepository;

    /**
     * @param \App\FrontendApi\Model\Blog\Article\BlogArticleElasticRepository $blogArticleElasticRepository
     */
    public function __construct(BlogArticleElasticRepository $blogArticleElasticRepository)
    {
        $this->blogArticleElasticRepository = $blogArticleElasticRepository;
    }

    /**
     * @param string $uuid
     * @return array
     */
    public function getByUuid(string $uuid): array
    {
        return $this->blogArticleElasticRepository->getByUuid($uuid);
    }
}
