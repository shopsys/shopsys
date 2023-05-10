export const parsePageNumberFromQuery = (query: string | string[] | undefined): number => {
    const parsedPageNumber = Number(query);
    return isNaN(parsedPageNumber) ? 1 : parsedPageNumber;
};
