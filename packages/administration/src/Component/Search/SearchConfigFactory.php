<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Search;

use Shopsys\AdministrationBundle\Component\Crud\Definition;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;

final class SearchConfigFactory
{
    public function create(AbstractCrudController $crudController, Definition $definition): SearchConfig
    {
        $searchConfig = new SearchConfig();

        $crudController->configureSearch($searchConfig);

        foreach ($definition->getExtensions() as $extension) {
            $extension->configureSearch($searchConfig);
        }

        return $searchConfig;
    }
}
