<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Breadcrumb;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlSlugBatchLoader;

class BreadcrumbLinksFactory
{
    public function __construct(
        protected readonly FriendlyUrlSlugBatchLoader $friendlyUrlSlugBatchLoader,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param array<int, array<int, \Shopsys\FrameworkBundle\Model\Category\Category|\Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory>> $entitiesInPaths
     * @return array<int, array<int, array{name: string, slug: string}>>
     */
    public function createByPaths(array $entitiesInPaths, string $routeName): array
    {
        $entityIdsInPaths = [];

        foreach ($entitiesInPaths as $entitiesInPath) {
            foreach ($entitiesInPath as $entityInPath) {
                $entityIdsInPaths[$entityInPath->getId()] = $entityInPath->getId();
            }
        }

        $slugsIndexedByEntityId = $this->friendlyUrlSlugBatchLoader->getSlugsIndexedByEntityId(
            array_values($entityIdsInPaths),
            $routeName,
        );
        $locale = $this->domain->getLocale();
        $breadcrumbLinks = [];

        foreach ($entitiesInPaths as $entitiesInPath) {
            $breadcrumbLinks[] = array_map(
                static fn (Category|BlogCategory $entityInPath): array => [
                    'name' => $entityInPath->getName($locale),
                    'slug' => $slugsIndexedByEntityId[$entityInPath->getId()],
                ],
                $entitiesInPath,
            );
        }

        return $breadcrumbLinks;
    }
}
