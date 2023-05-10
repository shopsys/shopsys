import { AutocompleteSearchQueryApi } from 'graphql/generated';
import { getGtmAutocompleteResultsViewEvent } from 'helpers/gtm/eventFactories';
import { gtmSafePushEvent } from 'helpers/gtm/gtm';
import { useEffect, useRef } from 'react';

export const useGtmAutocompleteResultsViewEvent = (
    searchResults: AutocompleteSearchQueryApi | undefined,
    searchQuery: string,
): void => {
    const lastViewedAutocompleteResults = useRef<AutocompleteSearchQueryApi>();

    useEffect(() => {
        if (searchResults !== undefined && lastViewedAutocompleteResults.current !== searchResults) {
            lastViewedAutocompleteResults.current = searchResults;
            gtmSafePushEvent(getGtmAutocompleteResultsViewEvent(searchResults, searchQuery));
        }
    }, [searchResults, searchQuery]);
};
