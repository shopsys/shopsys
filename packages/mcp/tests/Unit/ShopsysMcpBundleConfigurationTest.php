<?php

declare(strict_types=1);

namespace Tests\McpBundle\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\McpBundle\ShopsysMcpBundle;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ConfigurationExtensionInterface;

class ShopsysMcpBundleConfigurationTest extends TestCase
{
    public function testViewsSchemaNameDefaultsToMcp(): void
    {
        $config = $this->processConfiguration([]);

        $this->assertSame('mcp', $config['database']['views_schema_name']);
    }

    public function testDedicatedViewsSchemaNameIsAccepted(): void
    {
        $config = $this->processConfiguration(['database' => ['views_schema_name' => 'mcp_views']]);

        $this->assertSame('mcp_views', $config['database']['views_schema_name']);
    }

    #[DataProvider('provideNotDedicatedViewsSchemaNames')]
    public function testViewsSchemaNameMustBeDedicatedSchema(string $viewsSchemaName): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('must be a dedicated schema');

        $this->processConfiguration(['database' => ['views_schema_name' => $viewsSchemaName]]);
    }

    /**
     * @return iterable<string, array{viewsSchemaName: string}>
     */
    public static function provideNotDedicatedViewsSchemaNames(): iterable
    {
        yield 'public schema would be dropped on every migration' => [
            'viewsSchemaName' => 'public',
        ];

        yield 'information schema' => [
            'viewsSchemaName' => 'information_schema',
        ];

        yield 'system schema' => [
            'viewsSchemaName' => 'pg_catalog',
        ];

        yield 'temporary schema' => [
            'viewsSchemaName' => 'pg_temp',
        ];

        yield 'list of schemas would put public back on the search path' => [
            'viewsSchemaName' => 'mcp, public',
        ];
    }

    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    private function processConfiguration(array $config): array
    {
        $extension = (new ShopsysMcpBundle())->getContainerExtension();
        $this->assertInstanceOf(ConfigurationExtensionInterface::class, $extension);

        $configuration = $extension->getConfiguration([], new ContainerBuilder());
        $this->assertInstanceOf(ConfigurationInterface::class, $configuration);

        return (new Processor())->processConfiguration($configuration, [$config]);
    }
}
