import { render, screen } from '@testing-library/react';
import { Wishlist } from 'components/Pages/Wishlist/Wishlist';
import type { ReactNode } from 'react';
import { describe, expect, test, vi } from 'vitest';

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
    ProductsList: () => <div data-testid="products-list" />,
}));

vi.mock('components/Blocks/Skeleton/SkeletonModuleWishlist', () => ({
    SkeletonModuleWishlist: () => null,
}));

vi.mock('components/Forms/Button/Button', () => ({
    Button: ({
        children,
        className,
        'aria-label': ariaLabel,
    }: {
        children: ReactNode;
        className?: string;
        'aria-label'?: string;
    }) => (
        <button aria-label={ariaLabel} className={className} type="button">
            {children}
        </button>
    ),
}));

vi.mock('components/Layout/PageHero/PageHero', () => ({
    PageHero: () => null,
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
        t: (key: string) => key,
    }),
}));

vi.mock('utils/productLists/wishlist/useWishlist', () => ({
    useWishlist: () => ({
        isProductListFetching: false,
        removeWishlist: vi.fn(),
        wishlist: { products: Array.from({ length: 12 }, (_, index) => ({ uuid: `product-${index}` })) },
    }),
}));

describe('Wishlist', () => {
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
});
