import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';

export const getMappedProducts = (
    unmappedEdges: ({ node: TypeListedProductFragment | null } | null)[] | null | undefined,
): TypeListedProductFragment[] | undefined =>
    unmappedEdges?.reduce<TypeListedProductFragment[]>((mappedEdges, edge) => {
        if (edge?.node) {
            return [...mappedEdges, edge.node];
        }
        return mappedEdges;
    }, []);
