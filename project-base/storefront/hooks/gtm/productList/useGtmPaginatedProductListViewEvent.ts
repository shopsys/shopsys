import { DEFAULT_PAGE_SIZE } from 'components/Blocks/Pagination/Pagination';
import { ListedProductFragmentApi } from 'graphql/generated';
import { getGtmProductListViewEvent } from 'helpers/gtm/eventFactories';
import { gtmSafePushEvent } from 'helpers/gtm/gtm';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useQueryParams } from 'hooks/useQueryParams';
import { useEffect, useRef } from 'react';
import { GtmProductListNameType } from 'types/gtm/enums';

export const useGtmPaginatedProductListViewEvent = (
    paginatedProducts: ListedProductFragmentApi[] | undefined,
    gtmProductListName: GtmProductListNameType,
): void => {
    const lastViewedProducts = useRef<ListedProductFragmentApi[]>();
    const { currentPage } = useQueryParams();
    const { url } = useDomainConfig();

    useEffect(() => {
        if (paginatedProducts !== undefined && lastViewedProducts.current !== paginatedProducts) {
            lastViewedProducts.current = paginatedProducts;

            gtmSafePushEvent(
                getGtmProductListViewEvent(paginatedProducts, gtmProductListName, currentPage, DEFAULT_PAGE_SIZE, url),
            );
        }
    }, [gtmProductListName, currentPage, paginatedProducts, url]);
};
