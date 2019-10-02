<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\DependencyInjection;

use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ShopsysFrontendApiExtension extends Extension implements PrependExtensionInterface
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
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function load(array $config, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('parameters.yml');

        if ($container->getParameter('kernel.environment') === EnvironmentType::TEST) {
            $loader->load('services_test.yml');
        }
    }
}
