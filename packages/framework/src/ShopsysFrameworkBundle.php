<?php

namespace Shopsys\FrameworkBundle;

use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\AddConstraintValidatorsPass;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterCronModulesCompilerPass;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterExtendedEntitiesCompilerPass;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterMultiDesignFilesystemLoaderCompilerPass;
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
    public const VERSION = '11.1.0';

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
        $container->addCompilerPass(new RegisterExtendedEntitiesCompilerPass());
        $container->addCompilerPass(new AddConstraintValidatorsPass());
        $container->addCompilerPass(new RegisterMultiDesignFilesystemLoaderCompilerPass());

        $container->registerForAutoconfiguration(AbstractIndex::class)->addTag('elasticsearch.index');

        $environment = $container->getParameter('kernel.environment');

        if ($environment !== EnvironmentType::DEVELOPMENT) {
            return;
        }

        $container->addCompilerPass(new RegisterProjectFrameworkClassExtensionsCompilerPass());

        $container->addResource(new DirectoryResource($container->getParameter('kernel.project_dir') . '/src/Component'));
        $container->addResource(new DirectoryResource($container->getParameter('kernel.project_dir') . '/src/Model'));
    }
}
