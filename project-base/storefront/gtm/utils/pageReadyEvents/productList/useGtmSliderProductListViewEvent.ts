import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { useGtmContext } from 'gtm/context/GtmProvider';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { getGtmProductListViewEvent } from 'gtm/factories/getGtmProductListViewEvent';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { useEffect, useRef } from 'react';

export const useGtmSliderProductListViewEvent = (
    products: TypeListedProductFragment[] | undefined,
    gtmProuctListName: GtmProductListNameType,
    isLuigisEnabled?: boolean,
): void => {
    const wasViewedRef = useRef(false);
    const { url } = useDomainConfig();
    const { didPageReadyRun, isScriptLoaded } = useGtmContext();
    const { canSeePrices } = useAuthorization();

    useEffect(() => {
        if (isScriptLoaded && didPageReadyRun && products?.length && !wasViewedRef.current) {
            wasViewedRef.current = true;
            gtmSafePushEvent(
                getGtmProductListViewEvent(products, gtmProuctListName, 1, 0, url, !canSeePrices, isLuigisEnabled),
            );
        }
    }, [gtmProuctListName, products, url, didPageReadyRun, isScriptLoaded, canSeePrices, isLuigisEnabled]);
};
