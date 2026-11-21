<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\DependencyInjection\Compiler;

use Override;
use Shopsys\FrameworkBundle\DependencyInjection\ServicesResetter;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterResettableServicesCompilerPass implements CompilerPassInterface
{
    #[Override]
    public function process(ContainerBuilder $container): void
    {
        $resettableServices = [];

        foreach (array_keys($container->findTaggedServiceIds('kernel.reset', true)) as $serviceId) {
            // A strong reference (as created by !tagged_iterator) would keep otherwise unused services in the container,
            // including templates of child definitions whose abstract arguments make the container impossible to dump.
            // A service that was not initialized has no state to reset anyway.
            $resettableServices[$serviceId] = new Reference($serviceId, ContainerInterface::IGNORE_ON_UNINITIALIZED_REFERENCE);
        }

        $container->findDefinition(ServicesResetter::class)
            ->setArgument('$resettableServices', new IteratorArgument($resettableServices));
    }
}
