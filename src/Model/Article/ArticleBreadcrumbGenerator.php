<?php

declare(strict_types=1);

namespace App\Model\Article;

use App\Component\Breadcrumb\DomainBreadcrumbGeneratorInterface;
use Shopsys\FrameworkBundle\Model\Article\ArticleBreadcrumbGenerator as BaseArticleBreadcrumbGenerator;

/**
 * @property \App\Model\Article\ArticleRepository $articleRepository
 * @method __construct(\App\Model\Article\ArticleRepository $articleRepository)
 */
class ArticleBreadcrumbGenerator extends BaseArticleBreadcrumbGenerator implements DomainBreadcrumbGeneratorInterface
{
    /**
     * {@inheritDoc}
     */
    public function getBreadcrumbItemsOnDomain(int $domainId, string $routeName, array $routeParameters = [], ?string $locale = null): array
    {
        return $this->getBreadcrumbItems($routeName, $routeParameters);
    }
}
