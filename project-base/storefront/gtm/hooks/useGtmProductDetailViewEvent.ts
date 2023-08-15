import { MainVariantDetailFragmentApi, ProductDetailFragmentApi } from 'graphql/generated';
import { getGtmProductDetailViewEvent } from 'gtm/helpers/eventFactories';
import { gtmSafePushEvent } from 'gtm/helpers/gtm';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useEffect, useRef } from 'react';

export const useGtmProductDetailViewEvent = (
    productDetailData: ProductDetailFragmentApi | MainVariantDetailFragmentApi,
    slug: string,
    fetching: boolean,
): void => {
    const lastViewedProductDetailSlug = useRef<string | undefined>(undefined);
    const { url, currencyCode } = useDomainConfig();

    useEffect(() => {
        if (lastViewedProductDetailSlug.current !== slug && !fetching) {
            lastViewedProductDetailSlug.current = slug;
            gtmSafePushEvent(getGtmProductDetailViewEvent(productDetailData, currencyCode, url));
        }
    }, [productDetailData, currencyCode, slug, url, fetching]);
};
