import { Maybe } from 'graphql/generated';
import { useEffect, useRef } from 'react';
import { useShopsysSelector } from 'redux/main';
import { FriendlyUrlPageType } from 'types/friendlyUrl';
import { getGtmProductDetailEvent, getNewGtmEcommerceEvent } from 'utils/Gtm/EventFactories';
import { gtmSafePushEvent } from 'utils/Gtm/Gtm';

export const useGtmProductDetailView = (data: Maybe<FriendlyUrlPageType> | undefined, slug: string): void => {
    const event = getNewGtmEcommerceEvent('ec.product_view', true);
    const currencyCode = useShopsysSelector((state) => state.domain.currencyCode);
    const lastViewedProductDetailSlug = useRef<string | undefined>(undefined);

    useEffect(() => {
        if (
            data !== null &&
            (data.__typename === 'MainVariant' ||
                data.__typename === 'RegularProduct' ||
                data.__typename === 'Variant') &&
            lastViewedProductDetailSlug.current !== slug
        ) {
            lastViewedProductDetailSlug.current = slug;
            event.ecommerce = getGtmProductDetailEvent(data, currencyCode);
            gtmSafePushEvent(event);
        }
    }, [data, currencyCode, event, slug]);
};
