<?php

namespace Shopsys\FrameworkBundle\DependencyInjection;

use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbGeneratorInterface;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\GridInlineEditInterface;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataProviderInterface;
use Shopsys\FrameworkBundle\Twig\NoVarDumperExtension;
use Shopsys\FrameworkBundle\Twig\VarDumperExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class ShopsysFrameworkExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('directories.yaml');
        $loader->load('parameters_common.yaml');
        $loader->load('services.yaml');
        $loader->load('paths.yaml');

        if ($container->getParameter('kernel.environment') === EnvironmentType::DEVELOPMENT) {
            $loader->load('services_dev.yaml');
        }

        if ($container->getParameter('kernel.environment') === EnvironmentType::TEST) {
            $loader->load('services_test.yaml');
        }

        if ($container->getParameter('kernel.environment') === EnvironmentType::ACCEPTANCE) {
            $loader->load('services_acc.yaml');
        }

        $this->configureVarDumperTwigExtension($container);

        $container->registerForAutoconfiguration(GridInlineEditInterface::class)
            ->addTag('shopsys.grid_inline_edit');

        $container->registerForAutoconfiguration(FriendlyUrlDataProviderInterface::class)
            ->addTag('shopsys.friendly_url_provider');

        $container->registerForAutoconfiguration(BreadcrumbGeneratorInterface::class)
            ->addTag('shopsys.breadcrumb_generator');
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    protected function configureVarDumperTwigExtension(ContainerBuilder $container): void
    {
        $isDev = $container->getParameter('kernel.environment') === EnvironmentType::DEVELOPMENT;

        $varDumperExtensionService = $isDev ? VarDumperExtension::class : NoVarDumperExtension::class;

        $container->getDefinition($varDumperExtensionService)
            ->addTag('twig.extension');
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('doctrine_migrations', [
            'migrations_paths' => [
                'Shopsys\FrameworkBundle\Migrations' => __DIR__ . '/../Migrations',
            ],
        ]);
    }
}
