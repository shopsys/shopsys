<?php

declare(strict_types=1);

namespace Shopsys\FormTypesBundle;

use Override;
use Shopsys\FormTypesBundle\DependencyInjection\Compiler\OrderedResolvedFormTypeFactoryCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ShopsysFormTypesBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new OrderedResolvedFormTypeFactoryCompilerPass());
    }
}
