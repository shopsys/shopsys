type ConnectionEdges<Node> = ({ node: Node | null } | null)[] | null | undefined;

export function mapConnectionEdges<Node>(connectionEdges: ConnectionEdges<Node>): Node[] | undefined;
export function mapConnectionEdges<Node, MappedNode>(
    connectionEdges: ConnectionEdges<Node>,
    mapper: (node: Node) => MappedNode,
): MappedNode[] | undefined;
export function mapConnectionEdges<Node, MappedNode>(
    connectionEdges: ConnectionEdges<Node>,
    mapper?: (node: Node) => MappedNode,
): (Node | MappedNode)[] | undefined {
    return connectionEdges?.reduce((mappedEdges: (Node | MappedNode)[], edge) => {
        if (edge?.node) {
            mappedEdges.push(mapper ? mapper(edge.node) : edge.node);
        }

        return mappedEdges;
    }, []);
}
