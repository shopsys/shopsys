<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ShopsysLuigisBoxExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container): void
    {
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

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
        $loader->load('parameters.yaml');
    }
}
