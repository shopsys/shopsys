import { useEffect, useRef } from 'react';
import { useShopsysSelector } from 'redux/main';
import { SearchType } from 'types/search';
import { getGtmProductsListEvent, getNewGtmEcommerceEvent } from 'utils/Gtm/EventFactories';
import { gtmSafePushEvent } from 'utils/Gtm/Gtm';

export const useGtmSearchResultsListView = (data: SearchType | undefined, searchQuery: string): void => {
    const lastSearchQuery = useRef<string | undefined>(undefined);
    const lastViewedSearchPageStartCursor = useRef<string | undefined>(undefined);
    const { currentPage, pageSize } = useShopsysSelector((state) => state.user.pagination);

    useEffect(() => {
        if (
            data !== undefined &&
            (lastSearchQuery.current !== searchQuery ||
                lastViewedSearchPageStartCursor.current !== data.productsSearch.pageInfo.startCursor)
        ) {
            lastSearchQuery.current = searchQuery;
            lastViewedSearchPageStartCursor.current = data.productsSearch.pageInfo.startCursor;
            const event = getNewGtmEcommerceEvent('ec.products_list', true);
            event.ecommerce = getGtmProductsListEvent(data.productsSearch.products, 'search result', currentPage, pageSize);
            gtmSafePushEvent(event);
        }
    }, [data, searchQuery, currentPage, pageSize]);
};
