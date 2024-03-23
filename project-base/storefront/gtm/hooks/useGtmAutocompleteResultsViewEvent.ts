import { AutocompleteSearchQuery } from 'graphql/requests/search/queries/AutocompleteSearchQuery.generated';
import { useGtmContext } from 'gtm/context/useGtmContext';
import { getGtmAutocompleteResultsViewEvent } from 'gtm/helpers/eventFactories';
import { gtmSafePushEvent } from 'gtm/helpers/gtm';
import { useEffect, useRef } from 'react';

export const useGtmAutocompleteResultsViewEvent = (
    searchResults: AutocompleteSearchQuery | undefined,
    searchQuery: string,
): void => {
    const lastViewedAutocompleteResults = useRef<AutocompleteSearchQuery>();
    const { didPageViewRun } = useGtmContext();

    useEffect(() => {
        if (didPageViewRun && searchResults !== undefined && lastViewedAutocompleteResults.current !== searchResults) {
            lastViewedAutocompleteResults.current = searchResults;
            gtmSafePushEvent(getGtmAutocompleteResultsViewEvent(searchResults, searchQuery));
        }
    }, [searchResults, searchQuery, didPageViewRun]);
};
