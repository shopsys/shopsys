<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article;

use Override;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem;
use Shopsys\FrameworkBundle\Component\Breadcrumb\DomainBreadcrumbGeneratorInterface;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRouteNameEnum;

class ArticleBreadcrumbGenerator implements DomainBreadcrumbGeneratorInterface
{
    public function __construct(protected readonly ArticleRepository $articleRepository)
    {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getBreadcrumbItems(string $routeName, array $routeParameters = []): array
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
    public function getRouteNames(): array
    {
        return [FriendlyUrlRouteNameEnum::FRONT_ARTICLE_DETAIL];
    }
}
