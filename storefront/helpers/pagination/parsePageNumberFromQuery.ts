export const parsePageNumberFromQuery = (query: string | string[] | undefined): number => {
    const parsedPageNumber = Number(query);
    return Number.isNaN(parsedPageNumber) ? 1 : parsedPageNumber;
};
