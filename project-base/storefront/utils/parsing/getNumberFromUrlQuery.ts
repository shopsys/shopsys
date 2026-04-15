export const getNumberFromUrlQuery = (query: string | string[] | undefined, defaultNumber: number): number => {
    const parsedNumber = Number(query);
    return Number.isNaN(parsedNumber) ? defaultNumber : parsedNumber;
};
