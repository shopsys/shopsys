import { useEffect, useRef } from 'react';
import { GtmListNameType } from 'types/gtm';
import { ListedProductType } from 'types/product';
import { getGtmProductsListEvent, getNewGtmEcommerceEvent } from 'utils/Gtm/EventFactories';
import { gtmSafePushEvent } from 'utils/Gtm/Gtm';

export const useGtmSliderProductListView = (
    products: ListedProductType[] | undefined,
    listName: GtmListNameType,
): void => {
    const wasViewedRef = useRef(false);

    useEffect(() => {
        if (products !== undefined && !wasViewedRef.current) {
            wasViewedRef.current = true;

            const event = getNewGtmEcommerceEvent('ec.products_list', true);
            event.ecommerce = getGtmProductsListEvent(products, listName, 1, 0);
            gtmSafePushEvent(event);
        }
    }, [listName, products]);
};
