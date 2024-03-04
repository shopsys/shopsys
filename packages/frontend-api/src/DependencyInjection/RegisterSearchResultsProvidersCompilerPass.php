<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\DependencyInjection;

use Shopsys\FrontendApiBundle\Model\Resolver\Products\Search\ProductSearchResultsProviderResolver;
use Shopsys\FrontendApiBundle\Model\Resolver\Search\Exception\SearchResultsProviderPriorityNotSetException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RegisterSearchResultsProvidersCompilerPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container): void
    {
        foreach ($this->getSearchResultsProviderResolversIndexedByTag() as $serviceTag => $resolverServiceId) {
            $searchResultsProviderResolverDefinition = $container->getDefinition($resolverServiceId);
            $searchResultsProvidersDefinitions = $container->findTaggedServiceIds($serviceTag);

            foreach ($searchResultsProvidersDefinitions as $serviceId => $tags) {
                $priority = null;

                foreach ($tags as $tag) {
                    if (array_key_exists('priority', $tag)) {
                        $priority = $tag['priority'];
                    }
                }

                if (!is_int($priority)) {
                    throw new SearchResultsProviderPriorityNotSetException(sprintf('Service "%s" has not defined required tag priority or its type is not integer.', $serviceId));
                }

                $searchResultsProviderResolverDefinition->addMethodCall(
                    'registerSearchResultsProvider',
                    [
                        $serviceId,
                        $priority,
                    ],
                );
            }
        }
    }

    /**
     * @return array<string, string>
     */
    protected function getSearchResultsProviderResolversIndexedByTag(): array
    {
        return [
            'shopsys.frontend_api.products_search_results_provider' => ProductSearchResultsProviderResolver::class,
        ];
    }
}
