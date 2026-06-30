export const getMappedProducts = <TProduct>(
    unmappedEdges: ({ node: TProduct | null } | null)[] | null | undefined,
): TProduct[] | undefined =>
    unmappedEdges?.reduce<TProduct[]>((mappedEdges, edge) => {
        if (edge?.node) {
            return [...mappedEdges, edge.node];
        }
        return mappedEdges;
    }, []);
