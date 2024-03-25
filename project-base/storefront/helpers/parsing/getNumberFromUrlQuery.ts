export const getNumberFromUrlQuery = (query: string | string[] | undefined, defaultNumber: number): number => {
    const parsedNumber = Number(query);
    return isNaN(parsedNumber) ? defaultNumber : parsedNumber;
};
