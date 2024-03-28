import { TypeAutocompleteSearchQuery } from 'graphql/requests/search/queries/AutocompleteSearchQuery.generated';
import { useGtmContext } from 'gtm/context/useGtmContext';
import { getGtmAutocompleteResultsViewEvent } from 'gtm/factories/getGtmAutocompleteResultsViewEvent';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { useEffect, useRef } from 'react';

export const useGtmAutocompleteResultsViewEvent = (
    searchResults: TypeAutocompleteSearchQuery | undefined,
    TypeSearchQuery: string,
): void => {
    const lastViewedAutocompleteResults = useRef<TypeAutocompleteSearchQuery>();
    const { didPageViewRun } = useGtmContext();

    useEffect(() => {
        if (didPageViewRun && searchResults !== undefined && lastViewedAutocompleteResults.current !== searchResults) {
            lastViewedAutocompleteResults.current = searchResults;
            gtmSafePushEvent(getGtmAutocompleteResultsViewEvent(searchResults, TypeSearchQuery));
        }
    }, [searchResults, TypeSearchQuery, didPageViewRun]);
};
