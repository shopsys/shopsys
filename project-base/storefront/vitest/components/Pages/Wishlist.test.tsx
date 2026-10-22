import { fireEvent, render, screen, within } from '@testing-library/react';
import { Wishlist } from 'components/Pages/Wishlist/Wishlist';
import type { ReactNode } from 'react';
import { LOAD_MORE_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const mocks = vi.hoisted(() => ({
    query: {} as Record<string, string>,
    push: vi.fn(),
    replace: vi.fn(),
    productCount: 12,
    isProductListFetching: false,
}));

vi.mock('next/router', () => ({
    useRouter: () => ({
        pathname: '/wishlist',
        asPath: '/wishlist',
        query: mocks.query,
        push: mocks.push,
        replace: mocks.replace,
    }),
}));

vi.mock('utils/ui/useScrollRestoration', () => ({
    useScrollRestoration: () => undefined,
}));

vi.mock('components/Basic/Icon/HeartIcon', () => ({
    HeartIcon: () => null,
}));

vi.mock('components/Basic/Icon/TrashCanIcon', () => ({
    TrashCanIcon: () => null,
}));

vi.mock('components/Blocks/Product/LastVisitedProducts/DeferredLastVisitedProducts', () => ({
    DeferredLastVisitedProducts: () => null,
}));

vi.mock('components/Blocks/Product/ProductsList/ProductListViewModeToggle', () => ({
    ProductListViewModeToggle: () => <div data-testid="product-list-view-mode-toggle" />,
}));

vi.mock('components/Blocks/Product/ProductsList/ProductsList', () => ({
    ProductsList: ({ products }: { products: { uuid: string }[] }) => (
        <ul data-testid="products-list">
            {products.map((product) => (
                <li key={product.uuid}>{product.uuid}</li>
            ))}
        </ul>
    ),
}));

vi.mock('components/Blocks/Skeleton/SkeletonModuleWishlist', () => ({
    SkeletonModuleWishlist: () => null,
}));

vi.mock('components/Forms/Button/Button', () => ({
    Button: ({
        children,
        className,
        onClick,
        'aria-label': ariaLabel,
    }: {
        children: ReactNode;
        className?: string;
        onClick?: () => void;
        'aria-label'?: string;
    }) => (
        <button aria-label={ariaLabel} className={className} type="button" onClick={onClick}>
            {children}
        </button>
    ),
}));

vi.mock('components/Layout/PageHero/PageHero', () => ({
    PageHero: ({ description }: { description: string }) => <p>{description}</p>,
}));

vi.mock('components/Layout/VerticalStack/VerticalStack', () => ({
    VerticalStack: ({ children }: { children: ReactNode }) => <div>{children}</div>,
}));

vi.mock('components/Layout/Webline/Webline', () => ({
    Webline: ({ children }: { children: ReactNode }) => <div>{children}</div>,
}));

vi.mock('next/dynamic', () => ({
    default: () => () => null,
}));

vi.mock('store/useSessionStore', () => ({
    useSessionStore: (selector: (store: { updatePortalContent: ReturnType<typeof vi.fn> }) => unknown) =>
        selector({ updatePortalContent: vi.fn() }),
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string, values?: Record<string, unknown>) =>
            key.replace(/\{\{ (\w+) \}\}/g, (_, name: string) => String(values?.[name] ?? name)),
    }),
}));

vi.mock('utils/productLists/wishlist/useWishlist', () => ({
    useWishlist: () => ({
        isProductListFetching: mocks.isProductListFetching,
        removeWishlist: vi.fn(),
        wishlist: {
            products: Array.from({ length: mocks.productCount }, (_, index) => ({ uuid: `product-${index}` })),
        },
    }),
}));

describe('Wishlist', () => {
    beforeEach(() => {
        mocks.query = {};
        mocks.productCount = 12;
        mocks.isProductListFetching = false;
    });

    test('renders the product list view mode toggle for a non-empty wishlist', () => {
        render(<Wishlist />);

        expect(screen.getByTestId('product-list-view-mode-toggle')).toBeInTheDocument();
        expect(screen.getByTestId('products-list')).toBeInTheDocument();
    });

    test('keeps the title and compact remove button in the same row', () => {
        render(<Wishlist />);

        const title = screen.getByRole('heading', { name: 'Wishlist' });
        const removeButton = screen.getByRole('button', { name: 'Remove all product from wishlist' });

        expect(removeButton.parentElement).toBe(title.parentElement);
        expect(removeButton).toHaveTextContent('Remove all');
    });

    test('renders the product count and view toggle in the controls row', () => {
        render(<Wishlist />);

        const productCount = screen.getByText('12 products count');
        const viewModeToggle = screen.getByTestId('product-list-view-mode-toggle');

        expect(viewModeToggle.parentElement).toBe(productCount.parentElement);
    });

    test('renders only the first 30 products and keeps the full wishlist count', () => {
        mocks.productCount = 65;

        render(<Wishlist />);

        expect(within(screen.getByTestId('products-list')).getAllByRole('listitem')).toHaveLength(30);
        expect(screen.getByText('product-0')).toBeInTheDocument();
        expect(screen.queryByText('product-30')).not.toBeInTheDocument();
        expect(screen.getByText('65 products count')).toBeInTheDocument();
        expect(screen.getByRole('navigation', { name: 'Pagination navigation' })).toBeInTheDocument();
    });

    test('opens a page from the URL and changes pages through the shared pagination', () => {
        mocks.productCount = 65;
        mocks.query = { page: '2' };
        const { rerender } = render(<Wishlist />);

        expect(within(screen.getByTestId('products-list')).getAllByRole('listitem')).toHaveLength(30);
        expect(screen.getByText('product-30')).toBeInTheDocument();
        expect(screen.queryByText('product-0')).not.toBeInTheDocument();
        expect(screen.queryByText('product-60')).not.toBeInTheDocument();

        fireEvent.click(screen.getByRole('link', { name: 'Go to page 3' }));

        expect(mocks.push).toHaveBeenCalledWith(
            { pathname: '/wishlist', query: { page: '3' } },
            { pathname: '/wishlist', query: { page: '3' } },
            { shallow: true },
        );

        mocks.query = { page: '3' };
        rerender(<Wishlist />);

        expect(within(screen.getByTestId('products-list')).getAllByRole('listitem')).toHaveLength(5);
        expect(screen.getByText('product-60')).toBeInTheDocument();
        expect(screen.queryByRole('button', { name: /Load/ })).not.toBeInTheDocument();
    });

    test('loads more products and resets the loaded range when switching pages', () => {
        mocks.productCount = 65;
        const { rerender } = render(<Wishlist />);

        fireEvent.click(screen.getByRole('button', { name: 'Load 30 more products count' }));

        expect(mocks.push).toHaveBeenCalledWith(
            { pathname: '/wishlist', query: { [LOAD_MORE_QUERY_PARAMETER_NAME]: '1' } },
            { pathname: '/wishlist', query: { [LOAD_MORE_QUERY_PARAMETER_NAME]: '1' } },
            { shallow: true },
        );

        mocks.query = { [LOAD_MORE_QUERY_PARAMETER_NAME]: '1' };
        rerender(<Wishlist />);

        expect(within(screen.getByTestId('products-list')).getAllByRole('listitem')).toHaveLength(60);
        expect(screen.getByRole('button', { name: 'Load 5 more products count' })).toBeInTheDocument();

        fireEvent.click(screen.getByRole('link', { name: 'Go to page 3' }));

        expect(mocks.push).toHaveBeenLastCalledWith(
            { pathname: '/wishlist', query: { page: '3' } },
            { pathname: '/wishlist', query: { page: '3' } },
            { shallow: true },
        );
    });

    test('returns to the previous page after removing the last product on a page', () => {
        mocks.productCount = 61;
        mocks.query = { page: '3' };
        const { rerender } = render(<Wishlist />);

        expect(screen.getByText('product-60')).toBeInTheDocument();

        mocks.productCount = 60;
        rerender(<Wishlist />);

        expect(screen.getByText('product-30')).toBeInTheDocument();
        expect(within(screen.getByTestId('products-list')).getAllByRole('listitem')).toHaveLength(30);
        expect(mocks.push).not.toHaveBeenCalled();
        expect(mocks.replace).toHaveBeenCalledWith(
            { pathname: '/wishlist', query: { page: '2' } },
            { pathname: '/wishlist', query: { page: '2' } },
            { shallow: true },
        );
    });

    test.each(['0', '-1', '1.5', 'invalid', 'Infinity'])('normalizes an invalid page %s', (page) => {
        mocks.productCount = 65;
        mocks.query = { page };

        render(<Wishlist />);

        expect(screen.getByText('product-0')).toBeInTheDocument();
        expect(mocks.replace).toHaveBeenCalledWith(
            { pathname: '/wishlist', query: {} },
            { pathname: '/wishlist', query: {} },
            { shallow: true },
        );
    });

    test('clamps a page beyond the last page', () => {
        mocks.productCount = 65;
        mocks.query = { page: '10' };

        render(<Wishlist />);

        expect(screen.getByText('product-60')).toBeInTheDocument();
        expect(mocks.replace).toHaveBeenCalledWith(
            { pathname: '/wishlist', query: { page: '3' } },
            { pathname: '/wishlist', query: { page: '3' } },
            { shallow: true },
        );
    });

    test('does not change the requested page while the wishlist is loading', () => {
        mocks.query = { page: '3' };
        mocks.productCount = 0;
        mocks.isProductListFetching = true;

        render(<Wishlist />);

        expect(mocks.replace).not.toHaveBeenCalled();
        expect(screen.queryByRole('navigation')).not.toBeInTheDocument();
    });

    test('hides pagination for a wishlist that fits on one page', () => {
        mocks.productCount = 30;

        render(<Wishlist />);

        expect(within(screen.getByTestId('products-list')).getAllByRole('listitem')).toHaveLength(30);
        expect(screen.queryByRole('navigation')).not.toBeInTheDocument();
    });

    test('shows the empty state and resets pagination after clearing the wishlist', () => {
        mocks.productCount = 65;
        mocks.query = { page: '3' };
        const { rerender } = render(<Wishlist />);

        mocks.productCount = 0;
        rerender(<Wishlist />);

        expect(screen.getByText(/Your wishlist is feeling empty/)).toBeInTheDocument();
        expect(screen.queryByTestId('products-list')).not.toBeInTheDocument();
        expect(screen.queryByRole('navigation')).not.toBeInTheDocument();
        expect(mocks.replace).toHaveBeenCalledWith(
            { pathname: '/wishlist', query: {} },
            { pathname: '/wishlist', query: {} },
            { shallow: true },
        );
    });

    test.each(['-1', '1.5', 'invalid', 'Infinity'])('normalizes an invalid load-more value %s', (loadMore) => {
        mocks.productCount = 65;
        mocks.query = { page: '2', [LOAD_MORE_QUERY_PARAMETER_NAME]: loadMore };

        render(<Wishlist />);

        expect(within(screen.getByTestId('products-list')).getAllByRole('listitem')).toHaveLength(30);
        expect(screen.getByText('product-30')).toBeInTheDocument();
        expect(mocks.replace).toHaveBeenCalledWith(
            { pathname: '/wishlist', query: { page: '2' } },
            { pathname: '/wishlist', query: { page: '2' } },
            { shallow: true },
        );
        expect(mocks.push).not.toHaveBeenCalled();
    });

    test('loads additional products starting from a later page', () => {
        mocks.productCount = 95;
        mocks.query = { page: '2', [LOAD_MORE_QUERY_PARAMETER_NAME]: '1' };

        render(<Wishlist />);

        expect(within(screen.getByTestId('products-list')).getAllByRole('listitem')).toHaveLength(60);
        expect(screen.getByText('product-30')).toBeInTheDocument();
        expect(screen.getByText('product-89')).toBeInTheDocument();
        expect(screen.queryByText('product-90')).not.toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Load 5 more products count' })).toBeInTheDocument();
    });
});
