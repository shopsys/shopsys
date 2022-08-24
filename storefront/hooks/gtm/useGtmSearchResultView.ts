import { MINIMAL_SEARCH_QUERY_LENGTH } from 'connectors/search/AutocompleteSearch';
import { getGtmSearchResultEvent } from 'helpers/gtm/eventFactories';
import { gtmSafePushEvent } from 'helpers/gtm/gtm';
import { useEffect, useRef } from 'react';
import { AutocompleteSearchType } from 'types/search';

export const useGtmSearchResultView = (searchResult: AutocompleteSearchType | undefined, keyword: string): void => {
    const wasViewedRef = useRef(false);

    useEffect(() => {
        if (searchResult !== undefined && keyword.length >= MINIMAL_SEARCH_QUERY_LENGTH && !wasViewedRef.current) {
            wasViewedRef.current = true;
            const event = getGtmSearchResultEvent(searchResult, keyword);
            gtmSafePushEvent(event);
        }
    }, [keyword, searchResult]);
};
