<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Override;
use Shopsys\FrontendApiBundle\DependencyInjection\RegisterSearchResultsProvidersCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ShopsysFrontendApiBundle extends Bundle
{
    #[Override]
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterSearchResultsProvidersCompilerPass());
        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createAnnotationMappingDriver(
                [$this->getNamespace() . '\Model'],
                [$this->getPath() . '/Model'],
            ),
        );
    }
}
