import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { ListedProductFragmentApi } from 'graphql/generated';
import { useGtmContext } from 'gtm/context/useGtmContext';
import { getGtmProductListViewEvent } from 'gtm/helpers/eventFactories';
import { gtmSafePushEvent } from 'gtm/helpers/gtm';
import { GtmProductListNameType } from 'gtm/types/enums';
import { useQueryParams } from 'hooks/useQueryParams';
import { useEffect, useRef } from 'react';

export const useGtmPaginatedProductListViewEvent = (
    paginatedProducts: ListedProductFragmentApi[] | undefined,
    gtmProductListName: GtmProductListNameType,
): void => {
    const lastViewedStringifiedProducts = useRef<string>();
    const { currentPage, currentLoadMore } = useQueryParams();
    const previousLoadMoreRef = useRef(currentLoadMore);
    const { url } = useDomainConfig();
    const stringifiedProducts = JSON.stringify(paginatedProducts);
    const { didPageViewRun } = useGtmContext();

    useEffect(() => {
        if (didPageViewRun && paginatedProducts && lastViewedStringifiedProducts.current !== stringifiedProducts) {
            lastViewedStringifiedProducts.current = stringifiedProducts;

            let paginatedProductsSlice = paginatedProducts;
            if (previousLoadMoreRef.current !== currentLoadMore) {
                paginatedProductsSlice = paginatedProductsSlice.slice(currentLoadMore * DEFAULT_PAGE_SIZE);
                previousLoadMoreRef.current = currentLoadMore;
            }

            gtmSafePushEvent(
                getGtmProductListViewEvent(
                    paginatedProductsSlice,
                    gtmProductListName,
                    currentPage + currentLoadMore,
                    DEFAULT_PAGE_SIZE,
                    url,
                ),
            );
        }
    }, [gtmProductListName, currentPage, url, currentLoadMore, stringifiedProducts, didPageViewRun]);
};
