import { ListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';

export const getMappedProducts = (
    unmappedEdges: ({ node: ListedProductFragment | null } | null)[] | null | undefined,
): ListedProductFragment[] | undefined =>
    unmappedEdges?.reduce<ListedProductFragment[]>((mappedEdges, edge) => {
        if (edge?.node) {
            return [...mappedEdges, edge.node];
        }
        return mappedEdges;
    }, []);
