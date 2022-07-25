import { NextRouter } from 'next/router';
import { ParsedUrlQuery } from 'querystring';

export const getQueryWithoutAllParameter = (router: NextRouter): ParsedUrlQuery => {
    const routerQueryWithoutAllParameter = { ...router.query };
    delete routerQueryWithoutAllParameter.all;

    return routerQueryWithoutAllParameter;
};
