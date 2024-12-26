<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\DependencyInjection\Compiler;

use Shopsys\AdministrationBundle\Component\Attributes\CrudControllerExtension;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RegisterControllerExtensionsCompilerPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container): void
    {
        $container->registerAttributeForAutoconfiguration(CrudControllerExtension::class, static function (ChildDefinition $definition): void {
            $definition->clearTags();
            $definition->addTag('shopsys.admin.crud_controller_extension');
        });
    }
}
