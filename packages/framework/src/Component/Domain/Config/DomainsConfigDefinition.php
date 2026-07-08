<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Domain\Config;

use Override;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class DomainsConfigDefinition implements ConfigurationInterface
{
    public const string CONFIG_DOMAINS = 'domains';
    public const string CONFIG_ID = 'id';
    public const string CONFIG_NAME = 'name';
    public const string CONFIG_LOCALE = 'locale';
    public const string CONFIG_TIMEZONE = 'timezone';
    public const string CONFIG_TYPE = 'type';
    public const string CONFIG_LOAD_DEMO_DATA = 'load_demo_data';
    public const string CONFIG_CURRENCIES = 'currencies';

    #[Override]
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(self::CONFIG_DOMAINS);
        /** @var \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode(self::CONFIG_DOMAINS)
                    ->useAttributeAsKey(self::CONFIG_ID, false)
                    ->prototype('array')
                        ->children()
                            ->scalarNode(self::CONFIG_ID)->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode(self::CONFIG_NAME)->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode(self::CONFIG_LOCALE)->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode(self::CONFIG_TIMEZONE)->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode(self::CONFIG_TYPE)->defaultValue(
                                DomainConfig::TYPE_B2C,
                            )->end()
                            ->booleanNode(self::CONFIG_LOAD_DEMO_DATA)->defaultTrue()->end()
                            ->arrayNode(self::CONFIG_CURRENCIES)
                                ->info('List of currency codes enabled on the domain, the first one is the domain default currency')
                                ->isRequired()
                                ->requiresAtLeastOneElement()
                                ->scalarPrototype()->cannotBeEmpty()->end()
                                ->validate()
                                    ->ifTrue(static function (array $currencyCodes): bool {
                                        if (count($currencyCodes) !== count(array_unique($currencyCodes))) {
                                            return true;
                                        }

                                        foreach ($currencyCodes as $currencyCode) {
                                            if (preg_match('/^[A-Z]{3}$/', (string)$currencyCode) !== 1) {
                                                return true;
                                            }
                                        }

                                        return false;
                                    })
                                    ->thenInvalid('Domain currencies must be a list of unique 3-letter uppercase ISO 4217 codes, got %s')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
