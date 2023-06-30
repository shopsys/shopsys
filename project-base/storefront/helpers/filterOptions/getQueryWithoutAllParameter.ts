import { ParsedUrlQuery } from 'querystring';

export const getQueryWithoutSlugTypeParameter = (query: ParsedUrlQuery): ParsedUrlQuery => {
    const routerQueryWithoutAllParameter = { ...query };
    delete routerQueryWithoutAllParameter.slugType;

    return routerQueryWithoutAllParameter;
};

export const getQueryWithoutSlugTypeParameterFromQueryString = (query: string): string => {
    const queryParams = new URLSearchParams(query);
    queryParams.delete('slugType');

    return queryParams.toString();
};
