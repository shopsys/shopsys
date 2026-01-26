<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle;

use Override;
use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\AddConstraintValidatorsPass;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterContextsCompilerPass;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterCronModulesCompilerPass;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterExtendedEntitiesCompilerPass;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterImageEntitiesCompilerPass;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterPluginCrudExtensionsCompilerPass;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterPluginDataFixturesCompilerPass;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterProductFeedConfigsCompilerPass;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterProjectShopsysClassExtensionsCompilerPass;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterRoleProviderCompilerPass;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ShopsysFrameworkBundle extends Bundle
{
    /**
     * @var string
     */
    public const string VERSION = '19.0.0-dev';

    #[Override]
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterCronModulesCompilerPass());
        $container->addCompilerPass(new RegisterPluginCrudExtensionsCompilerPass());
        $container->addCompilerPass(new RegisterPluginDataFixturesCompilerPass());
        $container->addCompilerPass(new RegisterProductFeedConfigsCompilerPass());
        $container->addCompilerPass(new RegisterExtendedEntitiesCompilerPass());
        $container->addCompilerPass(new AddConstraintValidatorsPass());
        $container->addCompilerPass(new RegisterContextsCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 150);
        $container->addCompilerPass(new RegisterRoleProviderCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 140);
        $container->addCompilerPass(new RegisterImageEntitiesCompilerPass());

        $container->registerForAutoconfiguration(AbstractIndex::class)->addTag('elasticsearch.index');

        $environment = $container->getParameter('kernel.environment');

        if ($environment !== EnvironmentType::DEVELOPMENT) {
            return;
        }

        $container->addCompilerPass(new RegisterProjectShopsysClassExtensionsCompilerPass());

        $container->addResource(new DirectoryResource($container->getParameter('kernel.project_dir') . '/src/Component'));
        $container->addResource(new DirectoryResource($container->getParameter('kernel.project_dir') . '/src/Model'));
    }
}
