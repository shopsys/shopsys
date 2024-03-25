export const getQueryWithoutSlugTypeParameterFromQueryString = (query: string): string => {
    const queryParams = new URLSearchParams(query);
    queryParams.delete('slugType');

    return queryParams.toString();
};
