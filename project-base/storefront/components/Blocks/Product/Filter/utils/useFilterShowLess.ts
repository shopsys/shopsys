import { MappedFilterOption } from 'components/Blocks/Product/Filter/FilterGroupGeneric';
import { useState } from 'react';

export const useFilterShowLess = (
    options: MappedFilterOption[] | undefined,
    defaultNumberOfItems: number,
    selectedItems: string[] | null | undefined,
) => {
    const hiddenOptions = options?.slice(defaultNumberOfItems, options.length);
    const isWithHiddenCheckedItem = hiddenOptions?.some((o) => !!selectedItems?.includes(o.uuid));

    const [isWithAllItemsShown, setAreAllItemsShown] = useState(isWithHiddenCheckedItem);

    return {
        isShowLessMoreShown: !!hiddenOptions?.length,
        isWithAllItemsShown,
        setAreAllItemsShown,
    };
};
