<?php

declare(strict_types=1);

namespace App\Component\Breadcrumb;

use Exception;
use LogicException;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbResolver as BaseBreadcrumbResolver;
use Shopsys\FrameworkBundle\Component\Breadcrumb\Exception\BreadcrumbGeneratorNotFoundException;
use Shopsys\FrameworkBundle\Component\Breadcrumb\Exception\UnableToGenerateBreadcrumbItemsException;

class BreadcrumbResolver extends BaseBreadcrumbResolver
{
    /**
     * @var \App\Component\Breadcrumb\DomainBreadcrumbGeneratorInterface[]|\Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbGeneratorInterface[]
     */
    protected $breadcrumbGeneratorsByRouteName;

    /**
     * @param int $domainId
     * @param string $routeName
     * @param array $routeParameters
     * @param string|null $locale
     * @return \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem[]
     */
    public function resolveBreadcrumbItemsOnDomain(int $domainId, string $routeName, array $routeParameters = [], ?string $locale = null): array
    {
        if (!$this->hasGeneratorForRoute($routeName)) {
            throw new BreadcrumbGeneratorNotFoundException($routeName);
        }

        $breadcrumbGenerator = $this->breadcrumbGeneratorsByRouteName[$routeName];

        if (!$breadcrumbGenerator instanceof DomainBreadcrumbGeneratorInterface) {
            throw new LogicException(
                sprintf(
                    'Breadcrumb generator for route "%s" must implement "%s" to be able generate breadcrumbs for different domain ',
                    $routeName,
                    DomainBreadcrumbGeneratorInterface::class
                )
            );
        }

        try {
            return $breadcrumbGenerator->getBreadcrumbItemsOnDomain($domainId, $routeName, $routeParameters, $locale);
        } catch (Exception $ex) {
            throw new UnableToGenerateBreadcrumbItemsException($ex->getMessage());
        }
    }
}
