import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { ListedProductFragmentApi } from 'graphql/generated';
import { useGtmContext } from 'gtm/context/useGtmContext';
import { getGtmProductListViewEvent } from 'gtm/helpers/eventFactories';
import { gtmSafePushEvent } from 'gtm/helpers/gtm';
import { GtmProductListNameType } from 'gtm/types/enums';
import { useEffect, useRef } from 'react';

export const useGtmSliderProductListViewEvent = (
    products: ListedProductFragmentApi[] | undefined,
    gtmProuctListName: GtmProductListNameType,
): void => {
    const wasViewedRef = useRef(false);
    const { url } = useDomainConfig();
    const { didPageViewRun } = useGtmContext();

    useEffect(() => {
        if (didPageViewRun && products?.length && !wasViewedRef.current) {
            wasViewedRef.current = true;
            gtmSafePushEvent(getGtmProductListViewEvent(products, gtmProuctListName, 1, 0, url));
        }
    }, [gtmProuctListName, products, url, didPageViewRun]);
};
