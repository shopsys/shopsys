<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\DependencyInjection\Compiler;

use Override;
use Reflector;
use Shopsys\AdministrationBundle\Component\Attributes\CrudAction;
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

        // the controller.service_arguments tag makes the service public and injectable as a controller even when it lives outside a Controller/ directory,
        // class-level attributes (including the clearTags() above) are applied before method-level ones, so these tags are kept
        $container->registerAttributeForAutoconfiguration(CrudAction::class, static function (ChildDefinition $definition, CrudAction $attribute, Reflector $reflector): void {
            $definition->addTag('controller.service_arguments');
            $definition->addTag(LoadCrudActionsCompilerPass::CRUD_ACTION_TAG);
        });
    }
}
