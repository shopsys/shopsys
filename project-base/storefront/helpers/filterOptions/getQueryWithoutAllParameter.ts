import { ParsedUrlQuery } from 'querystring';

export const getQueryWithoutAllParameter = (query: ParsedUrlQuery): ParsedUrlQuery => {
    const routerQueryWithoutAllParameter = { ...query };
    delete routerQueryWithoutAllParameter.all;

    return routerQueryWithoutAllParameter;
};

export const getQueryWithoutAllParameterFromQueryString = (query: string): string => {
    const queryParams = new URLSearchParams(query);
    queryParams.delete('all');

    return queryParams.toString();
};
