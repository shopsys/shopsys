import { Maybe } from 'graphql/generated';
import { getGtmProductsListEvent, getNewGtmEcommerceEvent } from 'helpers/gtm/eventFactories';
import { gtmSafePushEvent } from 'helpers/gtm/gtm';
import { useEffect, useRef } from 'react';
import { useShopsysSelector } from 'redux/main';
import { FriendlyUrlPageType } from 'types/friendlyUrl';

export const useGtmBrandProductListView = (
    data: Maybe<FriendlyUrlPageType> | undefined,
    slug: string,
    fetching: boolean,
): void => {
    const lastViewedBrandSlug = useRef<string | undefined>(undefined);
    const lastViewedBrandPageStartCursor = useRef<string | undefined>(undefined);
    const { currentPage, pageSize } = useShopsysSelector((state) => state.user.pagination);
    const { url } = useShopsysSelector((state) => state.domain);

    useEffect(() => {
        if (
            data !== null &&
            data !== undefined &&
            data.__typename === 'Brand' &&
            (lastViewedBrandSlug.current !== slug ||
                lastViewedBrandPageStartCursor.current !== data.productConnection.pageInfo.startCursor) &&
            !fetching
        ) {
            lastViewedBrandSlug.current = slug;
            lastViewedBrandPageStartCursor.current = data.productConnection.pageInfo.startCursor;
            const event = getNewGtmEcommerceEvent('ec.products_list', true);
            event.ecommerce = getGtmProductsListEvent(
                data.productConnection.products,
                'brand',
                currentPage,
                pageSize,
                url,
            );
            gtmSafePushEvent(event);
        }
    }, [data, slug, currentPage, pageSize, url, fetching]);
};
