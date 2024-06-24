<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Blog\Article;

use DateTime;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;

class BlogArticleResolverMap extends ResolverMap
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade $blogCategoryFacade
     */
    public function __construct(
        protected readonly BlogCategoryFacade $blogCategoryFacade,
    ) {
    }

    /**
     * @return array
     */
    protected function map(): array
    {
        return [
            'BlogArticle' => [
                'blogCategories' => function (array $blogArticleData) {
                    return $this->blogCategoryFacade->getByIds($blogArticleData['categories']);
                },
                'publishDate' => static function (array $blogArticleData) {
                    return new DateTime($blogArticleData['publishDate']);
                },
                'createdAt' => static function (array $blogArticleData) {
                    return new DateTime($blogArticleData['createdAt']);
                },
                'slug' => static function (array $blogArticleData) {
                    return '/' . $blogArticleData['mainSlug'];
                },
                'link' => static function (array $blogArticleData) {
                    return $blogArticleData['url'];
                },
                'hreflangLinks' => function (array $blogArticleData) {
                    return $blogArticleData['hreflangLinks'];
                },
                'mainBlogCategoryUuid' => function (array $blogArticleData) {
                    return $blogArticleData['mainBlogCategoryUuid'];
                },
            ],
        ];
    }
}
