<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Breadcrumb;

use Exception;
use LogicException;
use Shopsys\FrameworkBundle\Component\Breadcrumb\Exception\BreadcrumbGeneratorNotFoundException;
use Shopsys\FrameworkBundle\Component\Breadcrumb\Exception\UnableToGenerateBreadcrumbItemsException;
use Webmozart\Assert\Assert;

class BreadcrumbResolver
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Breadcrumb\DomainBreadcrumbGeneratorInterface[]|\Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbGeneratorInterface[]
     */
    protected array $breadcrumbGeneratorsByRouteName;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbGeneratorInterface[] $breadcrumbGenerators
     */
    public function __construct(iterable $breadcrumbGenerators)
    {
        $this->registerGenerators($breadcrumbGenerators);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbGeneratorInterface[] $breadcrumbGenerators
     */
    protected function registerGenerators(iterable $breadcrumbGenerators): void
    {
        Assert::allIsInstanceOf($breadcrumbGenerators, BreadcrumbGeneratorInterface::class);

        foreach ($breadcrumbGenerators as $breadcrumbGenerator) {
            foreach ($breadcrumbGenerator->getRouteNames() as $routeName) {
                $this->breadcrumbGeneratorsByRouteName[$routeName] = $breadcrumbGenerator;
            }
        }
    }

    /**
     * @param string $routeName
     * @param array $routeParameters
     * @return \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem[]
     */
    public function resolveBreadcrumbItems(string $routeName, array $routeParameters = []): array
    {
        if (!$this->hasGeneratorForRoute($routeName)) {
            throw new BreadcrumbGeneratorNotFoundException($routeName);
        }

        $breadcrumbGenerator = $this->breadcrumbGeneratorsByRouteName[$routeName];

        try {
            return $breadcrumbGenerator->getBreadcrumbItems($routeName, $routeParameters);
        } catch (Exception $ex) {
            throw new UnableToGenerateBreadcrumbItemsException($ex);
        }
    }

    /**
     * @param string $routeName
     * @return bool
     */
    public function hasGeneratorForRoute(string $routeName): bool
    {
        return array_key_exists($routeName, $this->breadcrumbGeneratorsByRouteName);
    }

    /**
     * @param int $domainId
     * @param string $routeName
     * @param array $routeParameters
     * @param string|null $locale
     * @return \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem[]
     */
    public function resolveBreadcrumbItemsOnDomain(
        int $domainId,
        string $routeName,
        array $routeParameters = [],
        ?string $locale = null,
    ): array {
        if (!$this->hasGeneratorForRoute($routeName)) {
            throw new BreadcrumbGeneratorNotFoundException($routeName);
        }

        $breadcrumbGenerator = $this->breadcrumbGeneratorsByRouteName[$routeName];

        if (!$breadcrumbGenerator instanceof DomainBreadcrumbGeneratorInterface) {
            throw new LogicException(
                sprintf(
                    'Breadcrumb generator for route "%s" must implement "%s" to be able generate breadcrumbs for different domain ',
                    $routeName,
                    DomainBreadcrumbGeneratorInterface::class,
                ),
            );
        }

        try {
            return $breadcrumbGenerator->getBreadcrumbItemsOnDomain($domainId, $routeName, $routeParameters, $locale);
        } catch (Exception) {
            throw new UnableToGenerateBreadcrumbItemsException();
        }
    }
}
