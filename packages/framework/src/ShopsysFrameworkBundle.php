<?php

namespace Shopsys\FrameworkBundle;

use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\LazyRedisCompilerPass;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterCronModulesCompilerPass;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterExtendedEntitiesCompilerPass;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterPluginCrudExtensionsCompilerPass;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterPluginDataFixturesCompilerPass;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterProductFeedConfigsCompilerPass;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterProjectFrameworkClassExtensionsCompilerPass;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ShopsysFrameworkBundle extends Bundle
{
    /**
     * @var string
     */
    public const VERSION = '9.0.1-dev';

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterCronModulesCompilerPass());
        $container->addCompilerPass(new RegisterPluginCrudExtensionsCompilerPass());
        $container->addCompilerPass(new RegisterPluginDataFixturesCompilerPass());
        $container->addCompilerPass(new RegisterProductFeedConfigsCompilerPass());
        $container->addCompilerPass(new LazyRedisCompilerPass());
        $container->addCompilerPass(new RegisterExtendedEntitiesCompilerPass());

        $container->registerForAutoconfiguration(AbstractIndex::class)->addTag('elasticsearch.index');

        $environment = $container->getParameter('kernel.environment');
        if ($environment === EnvironmentType::DEVELOPMENT) {
            $container->addCompilerPass(new RegisterProjectFrameworkClassExtensionsCompilerPass());
            $container->addResource(new DirectoryResource($container->getParameter('kernel.root_dir') . '/Component'));
            $container->addResource(new DirectoryResource($container->getParameter('kernel.root_dir') . '/Model'));
        }
    }
}
