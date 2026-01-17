<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store;

use Override;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbGeneratorInterface;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use function t;

class StoresBreadcrumbGenerator implements BreadcrumbGeneratorInterface
{
    public function __construct(
        protected readonly StoreFacade $storeFacade,
    ) {
    }

    /**
     * @param string $routeName
     * @return array|\Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem[]
     */
    #[Override]
    public function getBreadcrumbItems($routeName, array $routeParameters = []): array
    {
        $breadcrumbItems[] = new BreadcrumbItem(
            t('Department stores', domain: Translator::CUSTOMER_TRANSLATION_DOMAIN),
            'front_stores',
        );

        if (array_key_exists('id', $routeParameters)) {
            $store = $this->storeFacade->getById((int)$routeParameters['id']);

            $breadcrumbItems[] = new BreadcrumbItem(
                $store->getName(),
            );
        }

        return $breadcrumbItems;
    }

    /**
     * @return string[]
     */
    #[Override]
    public function getRouteNames(): array
    {
        return ['front_stores_detail', 'front_stores'];
    }
}
