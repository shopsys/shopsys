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

    #[Override]
    public function getBreadcrumbItems(string $routeName, array $routeParameters = []): array
    {
        $article = $this->articleRepository->getById($routeParameters['id']);

        return [
            new BreadcrumbItem($article->getName()),
        ];
    }

    #[Override]
    public function getBreadcrumbItemsOnDomain(
        int $domainId,
        string $routeName,
        array $routeParameters = [],
        ?string $locale = null,
    ): array {
        return $this->getBreadcrumbItems($routeName, $routeParameters);
    }

    #[Override]
    public function getRouteNames(): array
    {
        return ['front_article_detail'];
    }
}
