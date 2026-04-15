import { ParsedUrlQuery } from 'node:querystring';

export const getQueryWithoutSlugTypeParameterFromParsedUrlQuery = (query: ParsedUrlQuery): ParsedUrlQuery => {
    const routerQueryWithoutAllParameter = { ...query };
    delete routerQueryWithoutAllParameter.slugType;

    return routerQueryWithoutAllParameter;
};
