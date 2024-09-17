import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { useGtmContext } from 'gtm/context/GtmProvider';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { getGtmProductListViewEvent } from 'gtm/factories/getGtmProductListViewEvent';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { useEffect, useRef } from 'react';

export const useGtmSliderProductListViewEvent = (
    products: TypeListedProductFragment[] | undefined,
    gtmProuctListName: GtmProductListNameType,
): void => {
    const wasViewedRef = useRef(false);
    const { url } = useDomainConfig();
    const { didPageViewRun, isScriptLoaded } = useGtmContext();
    const currentCustomerData = useCurrentCustomerData();

    useEffect(() => {
        if (isScriptLoaded && didPageViewRun && products?.length && !wasViewedRef.current) {
            wasViewedRef.current = true;
            gtmSafePushEvent(
                getGtmProductListViewEvent(
                    products,
                    gtmProuctListName,
                    1,
                    0,
                    url,
                    !!currentCustomerData?.arePricesHidden,
                ),
            );
        }
    }, [gtmProuctListName, products, url, didPageViewRun]);
};
