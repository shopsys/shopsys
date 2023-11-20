import { FilterOptionsUrlQueryType } from 'types/productFilter';

export const getFilterWithoutEmpty = (filter: FilterOptionsUrlQueryType | undefined | null) => {
    if (!filter) {
        return undefined;
    }

    const updatedFilter = { ...filter };

    (Object.keys(updatedFilter) as Array<keyof typeof updatedFilter>).forEach((key) => {
        const newFilterValue = updatedFilter[key];
        if (Array.isArray(newFilterValue) && newFilterValue.length === 0) {
            delete updatedFilter[key];
        }
    });

    return updatedFilter;
};
