import { ListedProductFragmentApi } from 'graphql/generated';
import { getGtmProductListViewEvent } from 'gtm/helpers/eventFactories';
import { gtmSafePushEvent } from 'gtm/helpers/gtm';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useEffect, useRef } from 'react';
import { GtmProductListNameType } from 'gtm/types/enums';

export const useGtmSliderProductListViewEvent = (
    products: ListedProductFragmentApi[] | undefined,
    gtmProuctListName: GtmProductListNameType,
): void => {
    const wasViewedRef = useRef(false);
    const { url } = useDomainConfig();

    useEffect(() => {
        if (products?.length && !wasViewedRef.current) {
            wasViewedRef.current = true;
            gtmSafePushEvent(getGtmProductListViewEvent(products, gtmProuctListName, 1, 0, url));
        }
    }, [gtmProuctListName, products, url]);
};
