import { Maybe } from 'graphql/generated';
import { getGtmProductsListEvent, getNewGtmEcommerceEvent } from 'helpers/gtm/eventFactories';
import { gtmSafePushEvent } from 'helpers/gtm/gtm';
import { useEffect, useRef } from 'react';
import { useShopsysSelector } from 'redux/main';
import { FriendlyUrlPageType } from 'types/friendlyUrl';

export const useGtmFlagProductListView = (
    data: Maybe<FriendlyUrlPageType> | undefined,
    slug: string,
    fetching: boolean,
): void => {
    const lastViewedFlagSlug = useRef<string | undefined>(undefined);
    const lastViewedFlagPageStartCursor = useRef<string | undefined>(undefined);
    const { currentPage, pageSize } = useShopsysSelector((state) => state.user.pagination);
    const { url } = useShopsysSelector((state) => state.domain);

    useEffect(() => {
        if (
            data !== null &&
            data !== undefined &&
            data.__typename === 'Flag' &&
            (lastViewedFlagSlug.current !== slug ||
                lastViewedFlagPageStartCursor.current !== data.productConnection.pageInfo.startCursor) &&
            !fetching
        ) {
            lastViewedFlagSlug.current = slug;
            lastViewedFlagPageStartCursor.current = data.productConnection.pageInfo.startCursor;
            const event = getNewGtmEcommerceEvent('ec.products_list', true);
            event.ecommerce = getGtmProductsListEvent(
                data.productConnection.products,
                'flag',
                currentPage,
                pageSize,
                url,
            );
            gtmSafePushEvent(event);
        }
    }, [data, slug, currentPage, pageSize, url, fetching]);
};
