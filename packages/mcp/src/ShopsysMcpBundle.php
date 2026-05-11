<?php

declare(strict_types=1);

namespace Shopsys\McpBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Override;
use Shopsys\McpBundle\Component\Database\Middleware\McpSessionSettingsMiddleware;
use Shopsys\McpBundle\Component\Database\Query\SqlExecutor;
use Shopsys\McpBundle\Component\Database\Query\SqlQueryValidator;
use Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpTokenFacade;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class ShopsysMcpBundle extends AbstractBundle
{
    protected const string MCP_INSTRUCTIONS = <<<'TEXT'
This server exposes MCP tools, not MCP resources.
Use the tools in this order: getDatabaseTableNames, then getDatabaseSchema, then executeSql.
Do not use SELECT * and always include a top-level LIMIT.
TEXT;

    #[Override]
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->arrayNode('authorization')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('access_token_ttl_seconds')
                            ->min(1)
                            ->defaultValue(2592000)
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('query')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('max_returned_rows')
                            ->min(1)
                            ->defaultValue(500)
                        ->end()
                        ->integerNode('statement_timeout_ms')
                            ->min(1)
                            ->defaultValue(10000)
                        ->end()
                        ->integerNode('lock_timeout_ms')
                            ->min(1)
                            ->defaultValue(1000)
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    #[Override]
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');

        $container->services()
            ->get(McpSessionSettingsMiddleware::class)
                ->arg('$statementTimeoutMilliseconds', $config['query']['statement_timeout_ms'])
                ->arg('$lockTimeoutMilliseconds', $config['query']['lock_timeout_ms'])
            ->get(SqlExecutor::class)
                ->arg('$maxReturnedRows', $config['query']['max_returned_rows'])
            ->get(SqlQueryValidator::class)
                ->arg('$maxReturnedRows', $config['query']['max_returned_rows'])
            ->get(AdministratorMcpTokenFacade::class)
                ->arg('$accessTokenTtlSeconds', $config['authorization']['access_token_ttl_seconds']);
    }

    #[Override]
    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->prependExtensionConfig('mcp', [
            'discovery' => [
                'scan_dirs' => [
                    'src',
                    Path::makeRelative(
                        Path::canonicalize(__DIR__),
                        $builder->getParameter('kernel.project_dir'),
                    ),
                ],
            ],
            'instructions' => self::MCP_INSTRUCTIONS,
        ]);
        $builder->prependExtensionConfig('doctrine_migrations', [
            'migrations_paths' => [
                'Shopsys\McpBundle\Migrations' => __DIR__ . '/Migrations',
            ],
        ]);
    }

    #[Override]
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createAttributeMappingDriver(
                [$this->getNamespace() . '\Model'],
                [$this->getPath() . '/src/Model'],
            ),
        );
    }
}
