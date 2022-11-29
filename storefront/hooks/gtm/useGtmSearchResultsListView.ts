import { DEFAULT_PAGE_SIZE } from 'components/Blocks/Pagination/Pagination';
import { usePaginationContext } from 'components/Blocks/Pagination/usePaginationContext';
import { getGtmProductsListEvent, getNewGtmEcommerceEvent } from 'helpers/gtm/eventFactories';
import { gtmSafePushEvent } from 'helpers/gtm/gtm';
import { useEffect, useRef } from 'react';
import { useShopsysSelector } from 'redux/main';
import { ListedProductType } from 'types/product';

export const useGtmSearchResultsListView = (products: ListedProductType[], searchQuery: string): void => {
    const lastSearchQuery = useRef<string | undefined>(undefined);
    const lastViewedSearchPage = useRef<number | undefined>(undefined);
    const [{ page }] = usePaginationContext();
    const { url } = useShopsysSelector((state) => state.domain);

    useEffect(() => {
        if (lastSearchQuery.current !== searchQuery || lastViewedSearchPage.current !== page) {
            lastSearchQuery.current = searchQuery;
            lastViewedSearchPage.current = page;
            const event = getNewGtmEcommerceEvent('ec.products_list', true);
            event.ecommerce = getGtmProductsListEvent(products, 'search result', page, DEFAULT_PAGE_SIZE, url);
            gtmSafePushEvent(event);
        }
    }, [products, searchQuery, page, url]);
};
