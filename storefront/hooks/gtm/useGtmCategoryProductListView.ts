import { Maybe } from 'graphql/generated';
import { getGtmProductsListEvent, getNewGtmEcommerceEvent } from 'helpers/gtm/eventFactories';
import { getCategoryOrSeoCategoryGtmListName, gtmSafePushEvent } from 'helpers/gtm/gtm';
import { useEffect, useRef } from 'react';
import { useShopsysSelector } from 'redux/main';
import { FriendlyUrlPageType } from 'types/friendlyUrl';

export const useGtmCategoryProductListView = (
    data: Maybe<FriendlyUrlPageType> | undefined,
    slug: string,
    fetching: boolean,
): void => {
    const lastViewedCategorySlug = useRef<string | undefined>(undefined);
    const lastViewedCategoryPageStartCursor = useRef<string | undefined>(undefined);
    const { currentPage, pageSize } = useShopsysSelector((state) => state.user.pagination);
    const { url } = useShopsysSelector((state) => state.domain);

    useEffect(() => {
        if (
            data !== null &&
            data !== undefined &&
            data.__typename === 'Category' &&
            (lastViewedCategorySlug.current !== slug ||
                lastViewedCategoryPageStartCursor.current !== data.productConnection.pageInfo.startCursor) &&
            !fetching
        ) {
            lastViewedCategorySlug.current = slug;
            lastViewedCategoryPageStartCursor.current = data.productConnection.pageInfo.startCursor;
            const event = getNewGtmEcommerceEvent('ec.products_list', true);

            event.ecommerce = getGtmProductsListEvent(
                data.productConnection.products,
                getCategoryOrSeoCategoryGtmListName(data, slug),
                currentPage,
                pageSize,
                url,
            );
            gtmSafePushEvent(event);
        }
    }, [data, slug, currentPage, pageSize, url, fetching]);
};
