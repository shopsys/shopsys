<?php

declare(strict_types=1);

namespace App\Model\Product\Flag;

use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbGeneratorInterface;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class FlagBreadcrumbGenerator implements BreadcrumbGeneratorInterface
{
    /**
     * @param \App\Model\Product\Flag\FlagFacade $flagFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(private FlagFacade $flagFacade, private Domain $domain)
    {
    }

    /**
     * {@inheritdoc}
     */
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
    public function getRouteNames(): array
    {
        return ['front_flag_detail'];
    }
}
