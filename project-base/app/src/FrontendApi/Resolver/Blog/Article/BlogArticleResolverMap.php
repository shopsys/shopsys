<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Blog\Article;

use App\Model\Blog\Category\BlogCategoryFacade;
use DateTime;
use Overblog\GraphQLBundle\Resolver\ResolverMap;

class BlogArticleResolverMap extends ResolverMap
{
    /**
     * @param \App\Model\Blog\Category\BlogCategoryFacade $blogCategoryFacade
     */
    public function __construct(
        private readonly BlogCategoryFacade $blogCategoryFacade,
    ) {
    }

    /**
     * @return mixed[]
     */
    protected function map(): array
    {
        return [
            'BlogArticle' => [
                'blogCategories' => function (array $blogArticleData) {
                    return $this->blogCategoryFacade->getByIds($blogArticleData['categories']);
                },
                'publishDate' => static function (array $blogArticleData): \DateTime {
                    return new DateTime($blogArticleData['publishedAt']);
                },
                'createdAt' => static function (array $blogArticleData): \DateTime {
                    return new DateTime($blogArticleData['createdAt']);
                },
                'slug' => static function (array $blogArticleData) {
                    return '/' . $blogArticleData['mainSlug'];
                },
                'link' => static function (array $blogArticleData) {
                    return $blogArticleData['url'];
                },
            ],
        ];
    }
}
