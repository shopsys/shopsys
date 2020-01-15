<?php

namespace Shopsys\FrameworkBundle;

use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\LazyRedisCompilerPass;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterCronModulesCompilerPass;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterPluginCrudExtensionsCompilerPass;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterPluginDataFixturesCompilerPass;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterProductFeedConfigsCompilerPass;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterProjectFrameworkClassExtensionsCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ShopsysFrameworkBundle extends Bundle
{
    /**
     * @var string
     */
    public const VERSION = '8.1.0';

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
        $environment = $container->getParameter('kernel.environment');
        if ($environment === EnvironmentType::DEVELOPMENT) {
            $container->addCompilerPass(new RegisterProjectFrameworkClassExtensionsCompilerPass());
        }
    }
}
