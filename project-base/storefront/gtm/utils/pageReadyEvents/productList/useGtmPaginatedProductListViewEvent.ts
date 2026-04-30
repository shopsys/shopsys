import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { useGtmContext } from 'gtm/context/GtmProvider';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { getGtmProductListViewEvent } from 'gtm/factories/getGtmProductListViewEvent';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { useEffect, useRef } from 'react';
import { useCurrentLoadMoreQuery } from 'utils/queryParams/useCurrentLoadMoreQuery';
import { useCurrentPageQuery } from 'utils/queryParams/useCurrentPageQuery';

export const useGtmPaginatedProductListViewEvent = (
    paginatedProducts: TypeListedProductFragment[] | undefined,
    gtmProductListName: GtmProductListNameType,
): void => {
    const lastViewedStringifiedProducts = useRef<string>(undefined);
    const currentPage = useCurrentPageQuery();
    const currentLoadMore = useCurrentLoadMoreQuery();
    const previousLoadMoreRef = useRef<number | undefined>(undefined);
    const { url } = useDomainConfig();
    const stringifiedProducts = JSON.stringify(paginatedProducts);
    const { didPageReadyRun, isScriptLoaded } = useGtmContext();
    const { canSeePrices } = useAuthorization();

    useEffect(() => {
        if (
            isScriptLoaded &&
            didPageReadyRun &&
            paginatedProducts &&
            lastViewedStringifiedProducts.current !== stringifiedProducts
        ) {
            lastViewedStringifiedProducts.current = stringifiedProducts;

            let paginatedProductsSlice = paginatedProducts;
            if (previousLoadMoreRef.current !== undefined && previousLoadMoreRef.current !== currentLoadMore) {
                paginatedProductsSlice = paginatedProductsSlice.slice(currentLoadMore * DEFAULT_PAGE_SIZE);
            }
            previousLoadMoreRef.current = currentLoadMore;

            gtmSafePushEvent(
                getGtmProductListViewEvent(
                    paginatedProductsSlice,
                    gtmProductListName,
                    currentPage + currentLoadMore,
                    DEFAULT_PAGE_SIZE,
                    url,
                    !canSeePrices,
                ),
            );
        }
    }, [
        gtmProductListName,
        currentPage,
        url,
        currentLoadMore,
        paginatedProducts,
        stringifiedProducts,
        didPageReadyRun,
        isScriptLoaded,
        canSeePrices,
    ]);
};
