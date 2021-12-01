<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Blog\Article;

use App\Model\Blog\Category\BlogCategoryFacade;
use App\Model\Product\ProductElasticsearchProvider;
use DateTime;
use Overblog\GraphQLBundle\Resolver\ResolverMap;

class BlogArticleResolverMap extends ResolverMap
{
    /**
     * @var \App\Model\Blog\Category\BlogCategoryFacade
     */
    private BlogCategoryFacade $blogCategoryFacade;

    /**
     * @var \App\Model\Product\ProductElasticsearchProvider
     */
    private ProductElasticsearchProvider $productElasticsearchProvider;

    /**
     * @param \App\Model\Blog\Category\BlogCategoryFacade $blogCategoryFacade
     * @param \App\Model\Product\ProductElasticsearchProvider $productElasticsearchProvider
     */
    public function __construct(BlogCategoryFacade $blogCategoryFacade, ProductElasticsearchProvider $productElasticsearchProvider)
    {
        $this->blogCategoryFacade = $blogCategoryFacade;
        $this->productElasticsearchProvider = $productElasticsearchProvider;
    }

    /**
     * @return array
     */
    protected function map()
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
                'products' => function (array $blogArticleData) {
                    return $this->productElasticsearchProvider->getVisibleProductsArrayByIds($blogArticleData['products']);
                },
            ],
        ];
    }
}
