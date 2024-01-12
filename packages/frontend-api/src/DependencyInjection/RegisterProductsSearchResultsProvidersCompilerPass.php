<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\DependencyInjection;

use Shopsys\FrontendApiBundle\Model\Resolver\Products\Search\Exception\ProductSearchResultsProviderPriorityNotSetException;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\Search\ProductSearchResultsProviderResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RegisterProductsSearchResultsProvidersCompilerPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container): void
    {
        $productSearchResultsProviderResolverDefinition = $container->getDefinition(ProductSearchResultsProviderResolver::class);
        $productSearchResultsProvidersDefinitions = $container->findTaggedServiceIds('shopsys.frontend_api.products_search_results_provider');

        foreach ($productSearchResultsProvidersDefinitions as $serviceId => $tags) {
            $priority = null;

            foreach ($tags as $tag) {
                if (array_key_exists('priority', $tag)) {
                    $priority = $tag['priority'];
                }
            }

            if (!is_int($priority)) {
                throw new ProductSearchResultsProviderPriorityNotSetException(sprintf('Service "%s" has not defined required tag priority or its type is not integer.', $serviceId));
            }

            $productSearchResultsProviderResolverDefinition->addMethodCall(
                'registerProductSearchResultsProvider',
                [
                    $serviceId,
                    $priority,
                ],
            );
        }
    }
}
