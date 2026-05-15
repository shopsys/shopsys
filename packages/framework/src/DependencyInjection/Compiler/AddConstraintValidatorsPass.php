<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\DependencyInjection\Compiler;

use Override;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AddConstraintValidatorsPass implements CompilerPassInterface
{
    public function __construct(
        protected readonly string $validatorFactoryServiceId = 'validator.validator_factory',
        protected readonly string $constraintValidatorTag = 'validator.constraint_validator',
    ) {
    }

    #[Override]
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition($this->validatorFactoryServiceId)) {
            return;
        }

        $validators = [];

        foreach ($container->findTaggedServiceIds($this->constraintValidatorTag, true) as $id => $attributes) {
            $definition = $container->getDefinition($id);

            if (isset($attributes[0]['alias'])) {
                $validators[$attributes[0]['alias']] = new Reference($id);
            }

            if ($definition->getClass() !== null) {
                $validators[$definition->getClass()] = new Reference($id);
            }
            $validators[$id] = new Reference($id);
        }

        $container
            ->getDefinition($this->validatorFactoryServiceId)
            ->replaceArgument(0, ServiceLocatorTagPass::register($container, $validators));
    }
}
