<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle;

use Shopsys\FrontendApiBundle\DependencyInjection\RegisterProductsSearchResultsProvidersCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ShopsysFrontendApiBundle extends Bundle
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterProductsSearchResultsProvidersCompilerPass());
    }
}
