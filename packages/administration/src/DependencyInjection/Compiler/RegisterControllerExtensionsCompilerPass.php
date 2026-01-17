<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\DependencyInjection\Compiler;

use Override;
use Shopsys\AdministrationBundle\Component\Attributes\CrudControllerExtension;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class RegisterControllerExtensionsCompilerPass implements CompilerPassInterface
{
    #[Override]
    public function process(ContainerBuilder $container): void
    {
        $container->registerAttributeForAutoconfiguration(CrudControllerExtension::class, static function (ChildDefinition $definition): void {
            $definition->clearTags();
            $definition->addTag('shopsys.admin.crud_controller_extension');
        });
    }
}
