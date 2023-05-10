export const parseFilterOptionsFromQuery = (filterQuery: string | string[] | undefined): string | undefined => {
    if (Array.isArray(filterQuery)) {
        return undefined;
    }

    return filterQuery;
};
