import { getQueryWithoutSlugTypeParameter } from 'helpers/filterOptions/getQueryWithoutAllParameter';
import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';
import { INTERNAL_QUERY_PARAMETERS } from 'helpers/queryParams/queryParamNames';
import { NextRouter } from 'next/router';

export const generateCanonicalUrl = (router: NextRouter, url: string): string | null => {
    const newQueryOverwrite: Record<string, string> = {};
    const queryWithoutAllParameter = getQueryWithoutSlugTypeParameter(router.query);

    for (const queryParam in queryWithoutAllParameter) {
        if ((INTERNAL_QUERY_PARAMETERS as string[]).includes(queryParam)) {
            const queryParamValue = queryWithoutAllParameter[queryParam]?.toString();

            if (queryParamValue !== undefined) {
                newQueryOverwrite[queryParam] = queryParamValue;
            }
        }
    }

    if (JSON.stringify(newQueryOverwrite) === JSON.stringify(queryWithoutAllParameter)) {
        return null;
    }

    const urlWithoutTrailingSlash = url.charAt(url.length - 1) === '/' ? url.slice(0, url.length - 1) : url;
    const queryParams = new URLSearchParams(newQueryOverwrite).toString();

    if (queryParams.length === 0) {
        return `${urlWithoutTrailingSlash}${getUrlWithoutGetParameters(router.asPath)}`;
    }

    return `${urlWithoutTrailingSlash}${getUrlWithoutGetParameters(router.asPath)}?${queryParams}`;
};
