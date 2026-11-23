import { render, screen } from '@testing-library/react';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import type { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const query = vi.hoisted(() => ({ page: 1, loadMore: 0 }));

vi.mock('utils/queryParams/useCurrentPageQuery', () => ({ useCurrentPageQuery: () => query.page }));
vi.mock('utils/queryParams/useCurrentLoadMoreQuery', () => ({ useCurrentLoadMoreQuery: () => query.loadMore }));
vi.mock('store/useCookiesStore', () => ({
    useCookiesStore: (selector: (store: { productListViewMode: string }) => unknown) =>
        selector({ productListViewMode: 'grid' }),
}));
vi.mock('utils/i18n/useTranslationWrapper', () => ({ default: () => ({ t: (key: string) => key }) }));
vi.mock('components/Blocks/Product/ProductsList/ProductsListContent', () => ({
    ProductsListContent: () => null,
}));
vi.mock('components/Blocks/Skeleton/SkeletonModuleProductListItem', () => ({
    SkeletonModuleProductListItem: () => <div data-testid="skeleton" />,
}));

const products = (count: number) =>
    Array.from({ length: count }, (_, i) => ({ uuid: `product-${i}` })) as TypeListedProductFragment[];

const renderLoadingMore = (productCount: number, totalCount?: number) =>
    render(
        <ProductsList
            isLoadingMoreProducts
            gtmMessageOrigin={GtmMessageOriginType.other}
            gtmProductListName={GtmProductListNameType.wishlist}
            products={products(productCount)}
            totalCount={totalCount}
        />,
    );

describe('ProductsList', () => {
    beforeEach(() => {
        query.page = 1;
        query.loadMore = 0;
    });

    test('shows a full page of skeletons while loading more when the total count is unknown', () => {
        query.loadMore = 1;
        renderLoadingMore(30);

        expect(screen.getAllByTestId('skeleton')).toHaveLength(30);
    });

    test('shows skeletons only for the products remaining to be loaded', () => {
        query.loadMore = 2;
        renderLoadingMore(60, 65);

        expect(screen.getAllByTestId('skeleton')).toHaveLength(5);
    });

    test('counts the remaining products from the current page', () => {
        query.page = 2;
        query.loadMore = 1;
        renderLoadingMore(30, 70);

        expect(screen.getAllByTestId('skeleton')).toHaveLength(10);
    });

    test('shows no skeletons when all products of the range are already displayed', () => {
        query.loadMore = 1;
        renderLoadingMore(60, 65);

        expect(screen.queryByTestId('skeleton')).not.toBeInTheDocument();
    });
});
