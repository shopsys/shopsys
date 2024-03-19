import { ListedProductFragmentApi, ProductDetailFragmentApi } from 'graphql/generated';

export const getMappedProducts = (
    unmappedEdges: ({ node: ListedProductFragmentApi | null } | null)[] | null | undefined,
): ListedProductFragmentApi[] | undefined =>
    unmappedEdges?.reduce<ListedProductFragmentApi[]>((mappedEdges, edge) => {
        if (edge?.node) {
            return [...mappedEdges, edge.node];
        }
        return mappedEdges;
    }, []);

export const getProductVariants = (product: ProductDetailFragmentApi) => {
    return (
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
        (product.__typename === 'Variant' && product.mainVariant?.variants) ||
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
        (product.__typename === 'MainVariant' && product.variants) ||
        undefined
    );
};
