<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article;

use IteratorAggregate;
use Shopsys\FrameworkBundle\Model\Blog\Article\Exception\BlogArticleNotFoundException;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class BlogArticlesIdsToBlogArticlesTransformer implements DataTransformerInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleFacade $blogArticleFacade
     */
    public function __construct(protected BlogArticleFacade $blogArticleFacade)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle[]|mixed $blogArticles
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
     * @return \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle[]
     */
    public function reverseTransform($blogArticlesIds): array
    {
        $blogArticles = [];

        if (is_array($blogArticlesIds)) {
            foreach ($blogArticlesIds as $blogArticlesId) {
                try {
                    $blogArticles[] = $this->blogArticleFacade->getById((int)$blogArticlesId);
                } catch (BlogArticleNotFoundException $e) {
                    throw new TransformationFailedException('Blog article not found', 0, $e);
                }
            }
        }

        return $blogArticles;
    }
}
