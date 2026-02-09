<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\DependencyInjection;

use Override;
use Shopsys\AdministrationBundle\Component\Configuration\AccessControlConfiguration;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;

class ShopsysAdministrationExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Register AccessControlConfiguration as a service
        $accessControlConfigDefinition = new Definition(AccessControlConfiguration::class);
        $accessControlConfigDefinition->setArguments([
            $config['access_control']['additional_excluded_route_names'],
        ]);
        $container->setDefinition(AccessControlConfiguration::class, $accessControlConfigDefinition);

        // Override Framework bundle parameter for RolesType simple permissions
        $container->setParameter('shopsys.administration.roles.simple_permissions', $config['roles']['simple_permissions']);

        // Load services
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yaml');
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getAlias(): string
    {
        return Configuration::EXTENSION_ALIAS;
    }

    #[Override]
    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('doctrine_migrations', [
            'migrations_paths' => [
                'Shopsys\AdministrationBundle\Migrations' => __DIR__ . '/../Migrations',
            ],
        ]);

        $thirdPartyBundlesViewFileLocator = (new FileLocator(__DIR__ . '/../../templates/bundles'));

        $container->loadFromExtension('twig', [
            'paths' => [
                $thirdPartyBundlesViewFileLocator->locate('TwigBundle') => 'Twig',
            ],
        ]);

        $this->autoRegisterFormThemes($container);
    }

    public function autoRegisterFormThemes(ContainerBuilder $container): void
    {
        $finder = new Finder();
        $themeDir = __DIR__ . '/../../templates/form';
        $themes = [];

        if (is_dir($themeDir)) {
            foreach ($finder->files()->in($themeDir)->name('*.html.twig') as $file) {
                $themes[] = sprintf('@ShopsysAdministration/form/%s', $file->getRelativePathname());
            }
        }

        if ($themes !== []) {
            $container->prependExtensionConfig('twig', [
                'form_themes' => $themes,
            ]);
        }
    }
}
