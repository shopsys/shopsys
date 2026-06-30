export const mergeItemEdges = <TEdges extends readonly unknown[] | null | undefined>(
    previousItemEdges?: TEdges,
    newItemEdges?: TEdges,
) => [...(previousItemEdges || []), ...(newItemEdges || [])];
