export const parseProductListSortFromQuery = (sortQuery: string | string[] | undefined): string | undefined => {
    if (Array.isArray(sortQuery)) {
        return undefined;
    }

    return sortQuery;
};
