<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Shopsys\FrontendApiBundle\DependencyInjection\RegisterSearchResultsProvidersCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ShopsysFrontendApiBundle extends Bundle
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
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
