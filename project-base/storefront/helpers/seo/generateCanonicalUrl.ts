import {
    getQueryWithoutSlugTypeParameterFromParsedUrlQuery,
    getUrlWithoutGetParameters,
} from 'helpers/parsing/urlParsing';
import { getStringWithoutTrailingSlash } from 'helpers/parsing/stringWIthoutSlash';
import { INTERNAL_QUERY_PARAMETERS } from 'helpers/queryParamNames';
import { NextRouter } from 'next/router';

export const generateCanonicalUrl = (router: NextRouter, url: string): string | null => {
    const newQueryOverwrite: Record<string, string> = {};
    const queryWithoutAllParameter = getQueryWithoutSlugTypeParameterFromParsedUrlQuery(router.query);

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

    const queryParams = new URLSearchParams(newQueryOverwrite).toString();

    if (queryParams.length === 0) {
        return `${getStringWithoutTrailingSlash(url)}${getUrlWithoutGetParameters(router.asPath)}`;
    }

    return `${getStringWithoutTrailingSlash(url)}${getUrlWithoutGetParameters(router.asPath)}?${queryParams}`;
};
