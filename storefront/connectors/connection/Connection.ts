export const mapConnectionEdges = <UnmappedNodeType, MappedNodeType>(
    connectionEdges: ({ node: UnmappedNodeType | null } | null)[] | null,
    mapper: (unmappedNode: UnmappedNodeType) => MappedNodeType,
): MappedNodeType[] => {
    return connectionEdges !== null && Array.isArray(connectionEdges)
        ? connectionEdges.reduce((mappedEdges: MappedNodeType[], edge) => {
              if (edge !== null && edge.node !== null) {
                  mappedEdges.push(mapper(edge.node));
              }

              return mappedEdges;
          }, [])
        : [];
};
