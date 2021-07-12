<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Blog\Article;

use App\Model\Blog\Category\BlogCategoryFacade;
use Overblog\GraphQLBundle\Resolver\ResolverMap;

class BlogArticleResolverMap extends ResolverMap
{
    /**
     * @var \App\Model\Blog\Category\BlogCategoryFacade
     */
    private BlogCategoryFacade $blogCategoryFacade;

    /**
     * @param \App\Model\Blog\Category\BlogCategoryFacade $blogCategoryFacade
     */
    public function __construct(BlogCategoryFacade $blogCategoryFacade)
    {
        $this->blogCategoryFacade = $blogCategoryFacade;
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
            ],
        ];
    }
}
