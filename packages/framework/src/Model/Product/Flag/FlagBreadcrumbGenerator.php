<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

use Override;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbGeneratorInterface;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class FlagBreadcrumbGenerator implements BreadcrumbGeneratorInterface
{
    public function __construct(
        protected readonly FlagFacade $flagFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getBreadcrumbItems($routeName, array $routeParameters = []): array
    {
        $breadcrumbItems = [];

        if (array_key_exists('id', $routeParameters)) {
            $id = (int)$routeParameters['id'];
            $flag = $this->flagFacade->getVisibleFlagById($id, $this->domain->getLocale());

            $breadcrumbItems[] = new BreadcrumbItem(
                $flag->getName(),
                'front_flag_detail',
                ['id' => $id],
            );
        }

        return $breadcrumbItems;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getRouteNames(): array
    {
        return ['front_flag_detail'];
    }
}
