<?php

declare(strict_types=1);

namespace Shopsys\ArticleFeed\LuigisBoxBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Override;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ShopsysArticleFeedLuigisBoxBundle extends Bundle
{
    #[Override]
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createAttributeMappingDriver(
                [$this->getNamespace() . '\Model'],
                [$this->getPath() . '/Model'],
            ),
        );
    }
}
