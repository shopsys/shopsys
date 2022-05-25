import { Maybe } from 'graphql/generated';
import { useEffect, useRef } from 'react';
import { useShopsysSelector } from 'redux/main';
import { FriendlyUrlPageType } from 'types/friendlyUrl';
import { getGtmProductsListEvent, getNewGtmEcommerceEvent } from 'utils/Gtm/EventFactories';
import { gtmSafePushEvent } from 'utils/Gtm/Gtm';

export const useGtmBrandProductListView = (data: Maybe<FriendlyUrlPageType> | undefined, slug: string): void => {
    const lastViewedBrandSlug = useRef<string | undefined>(undefined);
    const lastViewedBrandPageStartCursor = useRef<string | undefined>(undefined);
    const { currentPage, pageSize } = useShopsysSelector((state) => state.user.pagination);

    useEffect(() => {
        if (
            data !== null &&
            data !== undefined &&
            data.__typename === 'Brand' &&
            (lastViewedBrandSlug.current !== slug ||
                lastViewedBrandPageStartCursor.current !== data.productConnection.pageInfo.startCursor)
        ) {
            lastViewedBrandSlug.current = slug;
            lastViewedBrandPageStartCursor.current = data.productConnection.pageInfo.startCursor;
            const event = getNewGtmEcommerceEvent('ec.products_list', true);
            event.ecommerce = getGtmProductsListEvent(data.productConnection.products, 'brand', currentPage, pageSize);
            gtmSafePushEvent(event);
        }
    }, [data, slug, currentPage, pageSize]);
};
