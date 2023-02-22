import { useEffect, useMemo, useRef, useState } from 'react';

// TODO extend this while taking "load more" functionality from Tescoma
export const useListingForPagination = <T>(unmappedEdges: ({ node: T | null } | null)[] | null | undefined): [T[]] => {
    const mappedNodes = useMemo(() => {
        const updatedMappedNodes: T[] = [];

        if (unmappedEdges === undefined || unmappedEdges === null) {
            return updatedMappedNodes;
        }

        for (const unmappedEdge of unmappedEdges) {
            if (unmappedEdge !== null && unmappedEdge.node !== null) {
                updatedMappedNodes.push(unmappedEdge.node);
            }
        }

        return updatedMappedNodes;
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [JSON.stringify(unmappedEdges)]);
    const [itemList, setItemList] = useState(mappedNodes);
    const itemsRef = useRef(mappedNodes);

    useEffect(() => {
        setItemList(mappedNodes);
        itemsRef.current = mappedNodes;
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [itemsRef, mappedNodes]);

    return [itemList];
};
