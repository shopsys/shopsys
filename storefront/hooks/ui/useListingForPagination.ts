import { useEffect, useRef, useState } from 'react';

// TODO extend this while taking "load more" functionality from Tescoma
export const useListingForPagination = <T extends unknown>(items: T[]): [T[]] => {
    const [itemList, setItemList] = useState(items);
    const itemsRef = useRef(items);

    useEffect(() => {
        setItemList(items);
        itemsRef.current = items;
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [itemsRef, JSON.stringify(items)]);

    return [itemList];
};
