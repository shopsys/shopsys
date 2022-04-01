import { getGtmProductDetailEvent, getNewGtmEcommerceEvent } from 'utils/Gtm/EventFactories';
import { useEffect, useRef } from 'react';
import { FriendlyUrlPageType } from 'types/friendlyUrl';
import { gtmSafePushEvent } from 'utils/Gtm/Gtm';
import { Maybe } from 'graphql/generated';
import { useShopsysSelector } from 'redux/main';

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
