<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\ZboziBundle\DependencyInjection;

use Override;
use Shopsys\FrontendApiBundle\ShopsysFrontendApiBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class ShopsysProductFeedZboziExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
        $loader->load('paths.yaml');

        if ($this->isFrontendApiInstalled()) {
            $loader->load('services-frontend-api.yaml');
        }
    }

    #[Override]
    public function prepend(ContainerBuilder $container): void
    {
        if ($this->isFrontendApiInstalled()) {
            $config = [
                'definitions' => [
                    'mappings' => [
                        'types' => [
                            [
                                'type' => 'yaml',
                                'dir' => __DIR__ . '/../Resources/config/graphql-types',
                            ],
                        ],
                    ],
                ],
            ];
            $container->prependExtensionConfig('overblog_graphql', $config);
        }

        $container->prependExtensionConfig('doctrine_migrations', [
            'migrations_paths' => [
                'Shopsys\ProductFeed\ZboziBundle\Migrations' => __DIR__ . '/../Migrations',
            ],
        ]);
    }

    private function isFrontendApiInstalled(): bool
    {
        return class_exists(ShopsysFrontendApiBundle::class);
    }
}
