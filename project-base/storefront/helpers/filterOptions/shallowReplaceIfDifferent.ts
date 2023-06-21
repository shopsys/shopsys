import { getQueryWithoutAllParameter } from './getQueryWithoutAllParameter';
import { NextRouter } from 'next/router';
import { UrlObject } from 'url';

export const shallowReplaceIfDifferent: (
    router: NextRouter,
    ...parameters: Parameters<typeof router.replace>
) => void = (router, url, as, options = { scroll: false }) => {
    if (
        typeof url !== 'string' &&
        areUrlsDifferent(
            { pathname: router.asPath.split('?')[0], query: getQueryWithoutAllParameter(router.query) },
            url,
        )
    ) {
        options.scroll = false;
        router.replace(url, as, { ...options, shallow: true });
    }
};

const areUrlsDifferent = (oldUrl: UrlObject, newUrl: UrlObject) => {
    return JSON.stringify(oldUrl.query) !== JSON.stringify(newUrl.query) || oldUrl.pathname !== newUrl.pathname;
};
