<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article;

use Override;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem;
use Shopsys\FrameworkBundle\Component\Breadcrumb\DomainBreadcrumbGeneratorInterface;

class ArticleBreadcrumbGenerator implements DomainBreadcrumbGeneratorInterface
{
    public function __construct(protected readonly ArticleRepository $articleRepository)
    {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
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
    #[Override]
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
    #[Override]
    public function getRouteNames()
    {
        return ['front_article_detail'];
    }
}
