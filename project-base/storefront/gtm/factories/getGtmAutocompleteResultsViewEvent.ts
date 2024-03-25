import { AutocompleteSearchQuery } from 'graphql/requests/search/queries/AutocompleteSearchQuery.generated';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmAutocompleteResultsViewEventType } from 'gtm/types/events';

export const getGtmAutocompleteResultsViewEvent = (
    searchResult: AutocompleteSearchQuery,
    keyword: string,
): GtmAutocompleteResultsViewEventType => {
    const resultsCount =
        searchResult.categoriesSearch.totalCount +
        searchResult.productsSearch.totalCount +
        searchResult.brandSearch.length +
        searchResult.articlesSearch.length;
    const suggestResult: GtmAutocompleteResultsViewEventType['autocompleteResults'] = {
        keyword,
        results: resultsCount,
        sections: {
            category: searchResult.categoriesSearch.totalCount,
            product: searchResult.productsSearch.totalCount,
            brand: searchResult.brandSearch.length,
            article: searchResult.articlesSearch.length,
        },
    };

    return {
        event: GtmEventType.autocomplete_results_view,
        autocompleteResults: suggestResult,
        _clear: true,
    };
};
