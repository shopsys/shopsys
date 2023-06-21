export const mapConnectionEdges = <MappedNodeType>(
    connectionEdges: ({ node: unknown | null } | null)[] | null | undefined,
    mapper?: (unmappedNode: unknown) => MappedNodeType,
): MappedNodeType[] | undefined =>
    connectionEdges?.reduce((mappedEdges: MappedNodeType[], edge) => {
        if (edge && edge.node) {
            mappedEdges.push(mapper ? mapper(edge.node) : (edge.node as MappedNodeType));
        }

        return mappedEdges;
    }, []);
