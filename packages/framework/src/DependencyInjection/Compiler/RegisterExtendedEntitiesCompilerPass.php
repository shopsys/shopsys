<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\DependencyInjection\Compiler;

use Doctrine\ORM\Mapping\ClassMetadata;
use Override;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RegisterExtendedEntitiesCompilerPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    #[Override]
    public function process(ContainerBuilder $container): void
    {
        /** @var \Doctrine\ORM\Mapping\Driver\AttributeDriver $attributeReader */
        $attributeReader = $container->get('doctrine.orm.default_metadata_driver');

        $entityExtensionMap = [];
        $allClasses = $attributeReader->getAllClassNames();

        foreach ($allClasses as $class) {
            if (strpos($class, 'App\\') === 0) {
                $parentClass = get_parent_class($class);

                if (
                    $parentClass !== false
                    && strpos($parentClass, 'Shopsys\\') === 0
                    && !$attributeReader->isTransient($parentClass)
                ) {
                    $attributeReader->loadMetadataForClass($parentClass, new ClassMetadata($parentClass));
                    $entityExtensionMap[$parentClass] = $class;
                }
            }
        }

        $currentEntityExtensionMap = $container->getParameter('shopsys.entity_extension.map');
        $container->setParameter(
            'shopsys.entity_extension.map',
            array_merge($entityExtensionMap, $currentEntityExtensionMap),
        );
    }
}
