<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Blog\Article;

use App\Model\Blog\Category\BlogCategoryFacade;
use DateTime;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;

class BlogArticleResolverMap extends ResolverMap
{
    /**
     * @var \App\Model\Blog\Category\BlogCategoryFacade
     */
    private BlogCategoryFacade $blogCategoryFacade;

    /**
     * @var \Overblog\DataLoader\DataLoaderInterface
     */
    private DataLoaderInterface $productsVisibleByIdsBatchLoader;

    /**
     * @param \App\Model\Blog\Category\BlogCategoryFacade $blogCategoryFacade
     * @param \Overblog\DataLoader\DataLoaderInterface $productsVisibleByIdsBatchLoader
     */
    public function __construct(
        BlogCategoryFacade $blogCategoryFacade,
        DataLoaderInterface $productsVisibleByIdsBatchLoader
    ) {
        $this->blogCategoryFacade = $blogCategoryFacade;
        $this->productsVisibleByIdsBatchLoader = $productsVisibleByIdsBatchLoader;
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
                    return $this->productsVisibleByIdsBatchLoader->load($blogArticleData['products']);
                },
            ],
        ];
    }
}
