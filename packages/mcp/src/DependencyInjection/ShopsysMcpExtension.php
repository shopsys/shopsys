<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\DependencyInjection;

use Override;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Filesystem\Path;

class ShopsysMcpExtension extends Extension implements PrependExtensionInterface
{
    protected const string MCP_INSTRUCTIONS = <<<'TEXT'
This server exposes MCP tools, not MCP resources.
Use the tools in this order: getDatabaseTableNames, then getDatabaseSchema, then executeSql.
Do not use SELECT * and always include a top-level LIMIT.
TEXT;

    #[Override]
    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('mcp', [
            'discovery' => [
                'scan_dirs' => [
                    'src',
                    Path::makeRelative(
                        Path::canonicalize(__DIR__ . '/..'),
                        $container->getParameter('kernel.project_dir'),
                    ),
                ],
            ],
            'instructions' => self::MCP_INSTRUCTIONS,
        ]);
        $container->prependExtensionConfig('doctrine_migrations', [
            'migrations_paths' => [
                'Shopsys\McpBundle\Migrations' => __DIR__ . '/../Migrations',
            ],
        ]);
    }

    #[Override]
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('shopsys_mcp.authorization.access_token_ttl_seconds', $config['authorization']['access_token_ttl_seconds']);
        $container->setParameter('shopsys_mcp.query.max_returned_rows', $config['query']['max_returned_rows']);
        $container->setParameter('shopsys_mcp.query.statement_timeout_ms', $config['query']['statement_timeout_ms']);
        $container->setParameter('shopsys_mcp.query.lock_timeout_ms', $config['query']['lock_timeout_ms']);
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
    }
}
