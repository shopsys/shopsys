import { Maybe } from 'graphql/generated';
import { useEffect, useRef } from 'react';
import { useShopsysSelector } from 'redux/main';
import { FriendlyUrlPageType } from 'types/friendlyUrl';
import { getGtmProductsListEvent, getNewGtmEcommerceEvent } from 'utils/Gtm/EventFactories';
import { gtmSafePushEvent } from 'utils/Gtm/Gtm';

export const useGtmFlagProductListView = (data: Maybe<FriendlyUrlPageType> | undefined, slug: string): void => {
    const lastViewedFlagSlug = useRef<string | undefined>(undefined);
    const lastViewedFlagPageStartCursor = useRef<string | undefined>(undefined);
    const { currentPage, pageSize } = useShopsysSelector((state) => state.user.pagination);

    useEffect(() => {
        if (
            data !== null &&
            data !== undefined &&
            data.__typename === 'Flag' &&
            (lastViewedFlagSlug.current !== slug ||
                lastViewedFlagPageStartCursor.current !== data.productConnection.pageInfo.startCursor)
        ) {
            lastViewedFlagSlug.current = slug;
            lastViewedFlagPageStartCursor.current = data.productConnection.pageInfo.startCursor;
            const event = getNewGtmEcommerceEvent('ec.products_list', true);
            event.ecommerce = getGtmProductsListEvent(data.productConnection.products, 'flag', currentPage, pageSize);
            gtmSafePushEvent(event);
        }
    }, [data, slug, currentPage, pageSize]);
};
