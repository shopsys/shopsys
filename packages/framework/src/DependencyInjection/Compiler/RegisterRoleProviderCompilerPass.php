<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\DependencyInjection\Compiler;

use Override;
use Shopsys\FrameworkBundle\Component\Security\Role\Hierarchy\AbstractRoleHierarchyProvider;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleProviderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class RegisterRoleProviderCompilerPass implements CompilerPassInterface
{
    #[Override]
    public function process(ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(RoleProviderInterface::class)
            ->addTag('shopsys.role_provider');

        $container->registerForAutoconfiguration(AbstractRoleHierarchyProvider::class)
            ->addTag('shopsys.role_hierarchy_provider');
    }
}
