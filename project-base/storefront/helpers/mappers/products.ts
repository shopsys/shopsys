import { ListedProductFragmentApi } from 'graphql/generated';

export const getMappedProducts = (
    unmappedEdges: ({ node: ListedProductFragmentApi | null } | null)[] | null | undefined,
): ListedProductFragmentApi[] | undefined =>
    unmappedEdges?.reduce<ListedProductFragmentApi[]>((mappedEdges, edge) => {
        if (edge?.node) {
            return [...mappedEdges, edge.node];
        }
        return mappedEdges;
    }, []);
