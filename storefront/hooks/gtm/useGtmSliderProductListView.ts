import { ListedProductFragmentApi } from 'graphql/generated';
import { getGtmProductsListEvent, getNewGtmEcommerceEvent } from 'helpers/gtm/eventFactories';
import { gtmSafePushEvent } from 'helpers/gtm/gtm';
import { useEffect, useRef } from 'react';
import { useShopsysSelector } from 'redux/main';
import { GtmListNameType } from 'types/gtm';

export const useGtmSliderProductListView = (
    products: ListedProductFragmentApi[] | undefined,
    listName: GtmListNameType,
): void => {
    const wasViewedRef = useRef(false);
    const { url } = useShopsysSelector((state) => state.domain);

    useEffect(() => {
        if (products !== undefined && !wasViewedRef.current) {
            wasViewedRef.current = true;

            const event = getNewGtmEcommerceEvent('ec.products_list', true);
            event.ecommerce = getGtmProductsListEvent(products, listName, 1, 0, url);
            gtmSafePushEvent(event);
        }
    }, [listName, products, url]);
};
