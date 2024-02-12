<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\LuigisBoxBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ShopsysProductFeedLuigisBoxBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createAnnotationMappingDriver(
                [$this->getNamespace() . '\Model'],
                [$this->getPath() . '/Model'],
            ),
        );
    }
}
