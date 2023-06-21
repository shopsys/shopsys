<?php

declare(strict_types=1);

namespace App\Model\Article;

use App\Component\Breadcrumb\DomainBreadcrumbGeneratorInterface;
use Shopsys\FrameworkBundle\Model\Article\ArticleBreadcrumbGenerator as BaseArticleBreadcrumbGenerator;

class ArticleBreadcrumbGenerator extends BaseArticleBreadcrumbGenerator implements DomainBreadcrumbGeneratorInterface
{
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
}
