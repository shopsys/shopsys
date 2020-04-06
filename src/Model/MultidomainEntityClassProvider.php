<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\Product\Type\ProductTypeDomain;
use Shopsys\FrameworkBundle\Model\MultidomainEntityClassProvider as BaseMultidomainEntityClassProvider;

class MultidomainEntityClassProvider extends BaseMultidomainEntityClassProvider
{
    /**
     * @return array
     */
    public function getManualMultidomainEntitiesNames(): array
    {
        $entityNames = parent::getManualMultidomainEntitiesNames();

        $entityNames[] = ProductTypeDomain::class;

        return $entityNames;
    }
}
