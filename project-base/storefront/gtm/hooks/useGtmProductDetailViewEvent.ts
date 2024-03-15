import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { ProductDetailFragmentApi } from 'graphql/generated';
import { useGtmContext } from 'gtm/context/useGtmContext';
import { getGtmProductDetailViewEvent } from 'gtm/helpers/eventFactories';
import { gtmSafePushEvent } from 'gtm/helpers/gtm';
import { useEffect, useRef } from 'react';

export const useGtmProductDetailViewEvent = (
    productDetailData: ProductDetailFragmentApi,
    slug: string,
    fetching: boolean,
): void => {
    const lastViewedProductDetailSlug = useRef<string | undefined>(undefined);
    const { url, currencyCode } = useDomainConfig();
    const { didPageViewRun } = useGtmContext();

    useEffect(() => {
        if (didPageViewRun && lastViewedProductDetailSlug.current !== slug && !fetching) {
            lastViewedProductDetailSlug.current = slug;
            gtmSafePushEvent(getGtmProductDetailViewEvent(productDetailData, currencyCode, url));
        }
    }, [productDetailData, currencyCode, slug, url, fetching, didPageViewRun]);
};
