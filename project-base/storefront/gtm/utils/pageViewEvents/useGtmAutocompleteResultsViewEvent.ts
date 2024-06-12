import { TypeAutocompleteSearchQuery } from 'graphql/requests/search/queries/AutocompleteSearchQuery.generated';
import { useGtmContext } from 'gtm/context/GtmProvider';
import { getGtmAutocompleteResultsViewEvent } from 'gtm/factories/getGtmAutocompleteResultsViewEvent';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { useEffect, useRef } from 'react';

export const useGtmAutocompleteResultsViewEvent = (
    searchResults: TypeAutocompleteSearchQuery | undefined,
    keyword: string,
): void => {
    const lastViewedAutocompleteResults = useRef<TypeAutocompleteSearchQuery>();
    const { didPageViewRun, isScriptLoaded } = useGtmContext();

    useEffect(() => {
        if (
            isScriptLoaded &&
            didPageViewRun &&
            searchResults !== undefined &&
            lastViewedAutocompleteResults.current !== searchResults
        ) {
            lastViewedAutocompleteResults.current = searchResults;
            gtmSafePushEvent(getGtmAutocompleteResultsViewEvent(searchResults, keyword));
        }
    }, [searchResults, keyword, didPageViewRun]);
};
