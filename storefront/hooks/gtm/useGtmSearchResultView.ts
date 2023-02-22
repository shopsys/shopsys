import { MINIMAL_SEARCH_QUERY_LENGTH } from 'connectors/search/AutocompleteSearch';
import { AutocompleteSearchQueryApi } from 'graphql/generated';
import { getGtmSearchResultEvent } from 'helpers/gtm/eventFactories';
import { gtmSafePushEvent } from 'helpers/gtm/gtm';
import { useEffect, useRef } from 'react';

export const useGtmSearchResultView = (searchResult: AutocompleteSearchQueryApi | undefined, keyword: string): void => {
    const wasViewedRef = useRef(false);

    useEffect(() => {
        if (searchResult !== undefined && keyword.length >= MINIMAL_SEARCH_QUERY_LENGTH && !wasViewedRef.current) {
            wasViewedRef.current = true;
            const event = getGtmSearchResultEvent(searchResult, keyword);
            gtmSafePushEvent(event);
        }
    }, [keyword, searchResult]);
};
