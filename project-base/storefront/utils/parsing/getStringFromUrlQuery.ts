export const getStringFromUrlQuery = (urlQuery: string | string[] | undefined): string => {
    if (urlQuery === undefined || Array.isArray(urlQuery)) {
        return '';
    }

    return urlQuery.trim();
};
