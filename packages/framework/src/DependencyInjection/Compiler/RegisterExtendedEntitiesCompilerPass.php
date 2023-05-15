<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\DependencyInjection\Compiler;

use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RegisterExtendedEntitiesCompilerPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        /** @var \Doctrine\ORM\Mapping\Driver\AnnotationDriver $annotationReader */
        $annotationReader = $container->get('doctrine.orm.default_metadata_driver');

        $entityExtensionMap = [];
        $allClasses = $annotationReader->getAllClassNames();

        foreach ($allClasses as $class) {
            if (strpos($class, 'App\\') === 0) {
                $parentClass = get_parent_class($class);

                if (
                    $parentClass !== false
                    && strpos($parentClass, 'Shopsys\\') === 0
                    && !$annotationReader->isTransient($parentClass)
                ) {
                    $annotationReader->loadMetadataForClass($parentClass, new ClassMetadata($parentClass));
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
