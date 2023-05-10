<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Blog\Article;

use App\FrontendApi\Resolver\Blog\Article\Exception\BlogArticleNotFoundUserError;
use App\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchFacade;
use App\Model\Blog\Article\Exception\BlogArticleNotFoundException;
use Shopsys\FrontendApiBundle\Model\Error\InvalidArgumentUserError;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class BlogArticleQuery extends AbstractQuery
{
    /**
     * @param \App\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchFacade $blogArticleElasticsearchFacade
     */
    public function __construct(private readonly BlogArticleElasticsearchFacade $blogArticleElasticsearchFacade)
    {
    }

    /**
     * @param string|null $uuid
     * @param string|null $urlSlug
     * @return array
     */
    public function blogArticleByUuidOrUrlSlugQuery(?string $uuid = null, ?string $urlSlug = null): array
    {
        try {
            if ($uuid !== null) {
                $blogArticleData = $this->blogArticleElasticsearchFacade->getByUuid($uuid);
            } elseif ($urlSlug !== null) {
                $urlSlug = ltrim($urlSlug, '/');
                $blogArticleData = $this->blogArticleElasticsearchFacade->getBySlug($urlSlug);
            } else {
                throw new InvalidArgumentUserError('You need to provide argument \'uuid\' or \'urlSlug\'.');
            }
        } catch (BlogArticleNotFoundException $blogArticleNotFoundException) {
            throw new BlogArticleNotFoundUserError($blogArticleNotFoundException->getMessage());
        }

        return $blogArticleData;
    }
}
