import { TypeAutocompleteSearchQuery } from 'graphql/requests/search/queries/AutocompleteSearchQuery.generated';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmAutocompleteResultsViewEventType } from 'gtm/types/events';

export const getGtmAutocompleteResultsViewEvent = (
    searchResult: TypeAutocompleteSearchQuery,
    keyword: string,
): GtmAutocompleteResultsViewEventType => {
    const category = getAutocompleteSectionResultsCount(
        searchResult.categoriesSearch.totalCount,
        searchResult.categoriesSearch.edges?.length ?? 0,
    );
    const product = getAutocompleteSectionResultsCount(
        searchResult.productsSearch.totalCount,
        searchResult.productsSearch.edges?.length ?? 0,
    );
    const brand = searchResult.brandSearch.length;
    const article = searchResult.articlesSearch.length;

    const results = category + product + brand + article;

    const suggestResult: GtmAutocompleteResultsViewEventType['autocompleteResults'] = {
        keyword,
        results,
        sections: {
            category,
            product,
            brand,
            article,
        },
    };

    return {
        event: GtmEventType.autocomplete_results_view,
        autocompleteResults: suggestResult,
        _clear: true,
    };
};

const getAutocompleteSectionResultsCount = (totalCount: number, displayedCount: number): number =>
    totalCount >= 0 ? totalCount : displayedCount;
