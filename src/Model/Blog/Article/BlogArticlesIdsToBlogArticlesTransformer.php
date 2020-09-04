<?php

declare(strict_types=1);

namespace App\Model\Blog\Article;

use IteratorAggregate;
use Symfony\Component\Form\DataTransformerInterface;

class BlogArticlesIdsToBlogArticlesTransformer implements DataTransformerInterface
{
    /**
     * @var \App\Model\Blog\Article\BlogArticleFacade
     */
    private $blogArticleFacade;

    /**
     * @param \App\Model\Blog\Article\BlogArticleFacade $blogArticleFacade
     */
    public function __construct(BlogArticleFacade $blogArticleFacade)
    {
        $this->blogArticleFacade = $blogArticleFacade;
    }

    /**
     * @param \App\Model\Blog\Article\BlogArticle[]|mixed $blogArticles
     * @return int[]
     */
    public function transform($blogArticles): array
    {
        $blogArticlesIds = [];

        if (is_array($blogArticles) || ($blogArticles instanceof IteratorAggregate)) {
            foreach ($blogArticles as $blogArticle) {
                $blogArticlesIds[] = $blogArticle->getId();
            }
        }

        return $blogArticlesIds;
    }

    /**
     * @param int[] $blogArticlesIds
     * @return \App\Model\Blog\Article\BlogArticle[]
     */
    public function reverseTransform($blogArticlesIds): array
    {
        $blogArticles = [];

        if (is_array($blogArticlesIds)) {
            foreach ($blogArticlesIds as $blogArticlesId) {
                try {
                    $blogArticles[] = $this->blogArticleFacade->getById((int)$blogArticlesId);
                } catch (\App\Model\Blog\Article\Exception\BlogArticleNotFoundException $e) {
                    throw new \Symfony\Component\Form\Exception\TransformationFailedException('Blog article not found', 0, $e);
                }
            }
        }

        return $blogArticles;
    }
}
