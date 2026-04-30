import { TypeAutocompleteSearchQuery } from 'graphql/requests/search/queries/AutocompleteSearchQuery.generated';
import { useGtmContext } from 'gtm/context/GtmProvider';
import { getGtmAutocompleteResultsViewEvent } from 'gtm/factories/getGtmAutocompleteResultsViewEvent';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { useEffect, useRef } from 'react';

export const useGtmAutocompleteResultsViewEvent = (
    searchResults: TypeAutocompleteSearchQuery | undefined,
    keyword: string,
): void => {
    const lastViewedAutocompleteResults = useRef<TypeAutocompleteSearchQuery>(undefined);
    const { didPageReadyRun, isScriptLoaded } = useGtmContext();

    useEffect(() => {
        if (
            isScriptLoaded &&
            didPageReadyRun &&
            searchResults !== undefined &&
            lastViewedAutocompleteResults.current !== searchResults
        ) {
            lastViewedAutocompleteResults.current = searchResults;
            gtmSafePushEvent(getGtmAutocompleteResultsViewEvent(searchResults, keyword));
        }
    }, [searchResults, keyword, didPageReadyRun, isScriptLoaded]);
};
