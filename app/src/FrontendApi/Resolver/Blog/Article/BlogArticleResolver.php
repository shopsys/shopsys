<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Blog\Article;

use App\FrontendApi\Resolver\Blog\Article\Exception\BlogArticleNotFoundUserError;
use App\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchFacade;
use App\Model\Blog\Article\Exception\BlogArticleNotFoundException;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Shopsys\FrontendApiBundle\Model\Error\InvalidArgumentUserError;

class BlogArticleResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \App\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchFacade
     */
    private BlogArticleElasticsearchFacade $blogArticleElasticsearchFacade;

    /**
     * @param \App\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchFacade $blogArticleElasticsearchFacade
     */
    public function __construct(BlogArticleElasticsearchFacade $blogArticleElasticsearchFacade)
    {
        $this->blogArticleElasticsearchFacade = $blogArticleElasticsearchFacade;
    }

    /**
     * @param string|null $uuid
     * @param string|null $urlSlug
     * @return array
     */
    public function resolveByUuidOrUrlSlug(?string $uuid = null, ?string $urlSlug = null): array
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

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return [
            'resolveByUuidOrUrlSlug' => 'blogArticleByUuidOrUrlSlug',
        ];
    }
}
