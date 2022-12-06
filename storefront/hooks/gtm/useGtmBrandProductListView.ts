import { DEFAULT_PAGE_SIZE } from 'components/Blocks/Pagination/Pagination';
import { usePaginationContext } from 'components/Blocks/Pagination/usePaginationContext';
import { Maybe } from 'graphql/generated';
import { getGtmProductsListEvent, getNewGtmEcommerceEvent } from 'helpers/gtm/eventFactories';
import { gtmSafePushEvent } from 'helpers/gtm/gtm';
import { useEffect, useRef } from 'react';
import { useShopsysSelector } from 'redux/main';
import { FriendlyUrlPageType } from 'types/friendlyUrl';
import { ListedProductType } from 'types/product';

export const useGtmBrandProductListView = (
    data: Maybe<FriendlyUrlPageType> | undefined,
    slug: string,
    products: ListedProductType[],
    fetching: boolean,
): void => {
    const lastViewedBrandSlug = useRef<string | undefined>(undefined);
    const lastViewedBrandPage = useRef<number | undefined>(undefined);
    const [{ page }] = usePaginationContext();
    const { url } = useShopsysSelector((state) => state.domain);

    useEffect(() => {
        if (
            data !== null &&
            data !== undefined &&
            data.__typename === 'Brand' &&
            (lastViewedBrandSlug.current !== slug || lastViewedBrandPage.current !== page) &&
            !fetching
        ) {
            lastViewedBrandSlug.current = slug;
            lastViewedBrandPage.current = page;
            const event = getNewGtmEcommerceEvent('ec.products_list', true);
            event.ecommerce = getGtmProductsListEvent(products, 'brand', page, DEFAULT_PAGE_SIZE, url);
            gtmSafePushEvent(event);
        }
    }, [data, slug, page, url, fetching, products]);
};
