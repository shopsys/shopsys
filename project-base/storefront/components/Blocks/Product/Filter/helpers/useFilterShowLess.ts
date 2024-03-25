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

    const shownOptions = options?.slice(0, defaultNumberOfItems);
    const defaultOptions = isWithAllItemsShown ? options : shownOptions;

    return {
        isShowLessMoreShown: !!hiddenOptions?.length,
        defaultOptions,
        isWithAllItemsShown,
        setAreAllItemsShown,
    };
};
