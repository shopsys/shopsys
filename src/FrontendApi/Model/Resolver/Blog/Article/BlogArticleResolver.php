<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Blog\Article;

use App\FrontendApi\Model\Blog\Article\BlogArticleElasticsearchFacade;
use App\Model\Blog\Article\Exception\BlogArticleNotFoundException;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Error\UserError;

class BlogArticleResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \App\FrontendApi\Model\Blog\Article\BlogArticleElasticsearchFacade
     */
    private BlogArticleElasticsearchFacade $blogArticleElasticsearchFacade;

    /**
     * @param \App\FrontendApi\Model\Blog\Article\BlogArticleElasticsearchFacade $blogArticleElasticsearchFacade
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
                $blogArticleData = $this->blogArticleElasticsearchFacade->getBySlug($urlSlug);
            } else {
                throw new UserError('You need to provide argument \'uuid\' or \'urlSlug\'.');
            }
        } catch (BlogArticleNotFoundException $blogArticleNotFoundException) {
            throw new UserError($blogArticleNotFoundException->getMessage());
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
