<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article;

use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem;
use Shopsys\FrameworkBundle\Component\Breadcrumb\DomainBreadcrumbGeneratorInterface;

class ArticleBreadcrumbGenerator implements DomainBreadcrumbGeneratorInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleRepository $articleRepository
     */
    public function __construct(protected readonly ArticleRepository $articleRepository)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getBreadcrumbItems($routeName, array $routeParameters = [])
    {
        $article = $this->articleRepository->getById($routeParameters['id']);

        return [
            new BreadcrumbItem($article->getName()),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getBreadcrumbItemsOnDomain(
        int $domainId,
        string $routeName,
        array $routeParameters = [],
        ?string $locale = null,
    ): array {
        return $this->getBreadcrumbItems($routeName, $routeParameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteNames()
    {
        return ['front_article_detail'];
    }
}
