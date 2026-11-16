<?php

declare(strict_types=1);

namespace Shopsys\FormTypesBundle\DependencyInjection\Compiler;

use Override;
use Shopsys\FormTypesBundle\OrderedForm\OrderedResolvedFormTypeFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Form\Extension\DataCollector\Proxy\ResolvedTypeFactoryDataCollectorProxy;

/**
 * Taken over from Becklyn/OrderedFormBundle (BSD-3-Clause, Copyright (c) 2018, Becklyn Studios),
 * see open-source-license-acknowledgements-and-third-party-copyrights.md
 */
final class OrderedResolvedFormTypeFactoryCompilerPass implements CompilerPassInterface
{
    #[Override]
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition('form.resolved_type_factory');

        if ($definition->getClass() === ResolvedTypeFactoryDataCollectorProxy::class) {
            // in debug mode the factory is wrapped by the data collector proxy, replacing only the wrapped factory keeps the profiler working
            $definition->setArgument(0, new Reference(OrderedResolvedFormTypeFactory::class));
        } else {
            $definition->setClass(OrderedResolvedFormTypeFactory::class);
        }
    }
}
