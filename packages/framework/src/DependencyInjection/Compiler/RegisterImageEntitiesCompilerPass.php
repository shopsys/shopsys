<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\DependencyInjection\Compiler;

use Override;
use ReflectionAttribute;
use ReflectionClass;
use Shopsys\FrameworkBundle\Component\Image\Config\Attributes\EntityImage;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfigLoader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class RegisterImageEntitiesCompilerPass implements CompilerPassInterface
{
    #[Override]
    public function process(ContainerBuilder $container): void
    {
        /** @var \Doctrine\ORM\Mapping\Driver\AttributeDriver $attributeReader */
        $attributeReader = $container->get('doctrine.orm.default_metadata_driver');
        $allClasses = $attributeReader->getAllClassNames();

        $classesWithAttribute = $this->findClassesWithAttribute($allClasses);

        $definition = new Definition(ImageConfig::class);
        $definition->setFactory([new Reference(ImageConfigLoader::class), 'loadFromEntityClasses']);
        $definition->setArguments([$classesWithAttribute]);

        $container->setDefinition(ImageConfig::class, $definition);
    }

    /**
     * @param string[] $allClasses
     * @return string[]
     */
    protected function findClassesWithAttribute(array $allClasses): array
    {
        $classesWithAttribute = [];

        foreach ($allClasses as $class) {
            $reflectionClass = new ReflectionClass($class);
            $attributes = $reflectionClass->getAttributes(EntityImage::class, ReflectionAttribute::IS_INSTANCEOF);

            if (count($attributes) > 0) {
                $classesWithAttribute[] = $class;
            }
        }

        return $classesWithAttribute;
    }
}
