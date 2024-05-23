import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeMainVariantDetailFragment } from 'graphql/requests/products/fragments/MainVariantDetailFragment.generated';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { useGtmContext } from 'gtm/context/GtmProvider';
import { getGtmProductDetailViewEvent } from 'gtm/factories/getGtmProductDetailViewEvent';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { useEffect, useRef } from 'react';

export const useGtmProductDetailViewEvent = (
    productDetailData: TypeProductDetailFragment | TypeMainVariantDetailFragment,
    slug: string,
    fetching: boolean,
): void => {
    const lastViewedProductDetailSlug = useRef<string | undefined>(undefined);
    const { url, currencyCode } = useDomainConfig();
    const { didPageViewRun, isScriptLoaded } = useGtmContext();

    useEffect(() => {
        if (isScriptLoaded && didPageViewRun && lastViewedProductDetailSlug.current !== slug && !fetching) {
            lastViewedProductDetailSlug.current = slug;
            gtmSafePushEvent(getGtmProductDetailViewEvent(productDetailData, currencyCode, url));
        }
    }, [productDetailData, currencyCode, slug, url, fetching, didPageViewRun]);
};
