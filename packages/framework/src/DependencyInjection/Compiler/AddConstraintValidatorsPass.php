<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AddConstraintValidatorsPass implements CompilerPassInterface
{
    private $validatorFactoryServiceId;

    private $constraintValidatorTag;

    /**
     * @param string $validatorFactoryServiceId
     * @param string $constraintValidatorTag
     */
    public function __construct(
        string $validatorFactoryServiceId = 'validator.validator_factory',
        string $constraintValidatorTag = 'validator.constraint_validator'
    ) {
        $this->validatorFactoryServiceId = $validatorFactoryServiceId;
        $this->constraintValidatorTag = $constraintValidatorTag;
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
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

            $validators[$definition->getClass()] = new Reference($id);
            $validators[$id] = new Reference($id);
        }

        $container
            ->getDefinition($this->validatorFactoryServiceId)
            ->replaceArgument(0, ServiceLocatorTagPass::register($container, $validators));
    }
}
