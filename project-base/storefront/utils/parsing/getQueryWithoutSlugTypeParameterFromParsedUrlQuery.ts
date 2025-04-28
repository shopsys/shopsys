import { ParsedUrlQuery } from 'querystring';

export const getQueryWithoutSlugTypeParameterFromParsedUrlQuery = (
    query: ParsedUrlQuery | URLSearchParams | null,
): ParsedUrlQuery => {
    if (!query) {
        return {};
    }

    let parsedQuery: ParsedUrlQuery;

    if (query instanceof URLSearchParams) {
        parsedQuery = Object.fromEntries(query.entries());
    } else {
        parsedQuery = query;
    }

    const routerQueryWithoutAllParameter = { ...parsedQuery };
    delete routerQueryWithoutAllParameter.slugType;

    return routerQueryWithoutAllParameter;
};
