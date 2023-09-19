import { getUrlWithoutGetParameters } from 'helpers/parsing/urlParsing';
import { getStringWithoutTrailingSlash } from 'helpers/parsing/stringWIthoutSlash';
import { NextRouter } from 'next/router';
import {
    PAGE_QUERY_PARAMETER_NAME,
    SEARCH_QUERY_PARAMETER_NAME,
    FILTER_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
} from 'helpers/queryParamNames';

const DEFAULT_CANONICAL_QUERY_PARAMS = [
    PAGE_QUERY_PARAMETER_NAME,
    SEARCH_QUERY_PARAMETER_NAME,
    FILTER_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
] as const;

export type CanonicalQueryParameters = (typeof DEFAULT_CANONICAL_QUERY_PARAMS)[number][];

export const generateCanonicalUrl = (
    router: NextRouter,
    url: string,
    canonicalQueryParams?: CanonicalQueryParameters,
): string | null => {
    const newQueryOverwrite: Record<string, string> = {};
    const queries = router.query;

    for (const queryParam in queries) {
        if ((canonicalQueryParams || DEFAULT_CANONICAL_QUERY_PARAMS).includes(queryParam as any)) {
            const queryParamValue = queries[queryParam]?.toString();

            if (queryParamValue) {
                newQueryOverwrite[queryParam] = queryParamValue;
            }
        }
    }

    if (JSON.stringify(newQueryOverwrite) === JSON.stringify(queries)) {
        return null;
    }

    const queryParams = new URLSearchParams(newQueryOverwrite).toString();
    const canonicalUrl = `${getStringWithoutTrailingSlash(url)}${getUrlWithoutGetParameters(router.asPath)}`;

    if (!queryParams.length) {
        return canonicalUrl;
    }

    return `${canonicalUrl}?${queryParams}`;
};
