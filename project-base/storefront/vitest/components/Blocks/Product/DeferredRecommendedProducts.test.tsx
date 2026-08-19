import { render, screen } from '@testing-library/react';
import { DeferredRecommendedProducts } from 'components/Blocks/Product/DeferredRecommendedProducts';
import { TypeRecommendationType } from 'graphql/types';
import type { ReactElement } from 'react';
import { describe, expect, test, vi } from 'vitest';

const mockedUseRecommendedProductsQuery = vi.fn();

vi.mock('components/Blocks/Skeleton/SkeletonModuleProductSlider', () => ({
    SkeletonModuleProductSlider: ({
        isHeadingHidden,
        productItemProps,
        variant,
        visibleSliderItems,
    }: {
        isHeadingHidden?: boolean;
        productItemProps?: {
            size?: string;
            visibleItemsConfig?: {
                addToCart?: boolean;
                storeAvailability?: boolean;
            };
        };
        variant?: string;
        visibleSliderItems?: number;
    }) => (
        <div
            data-add-to-cart={productItemProps?.visibleItemsConfig?.addToCart}
            data-heading-hidden={isHeadingHidden}
            data-size={productItemProps?.size}
            data-store-availability={productItemProps?.visibleItemsConfig?.storeAvailability}
            data-testid="recommended-products-skeleton"
            data-variant={variant}
            data-visible-slider-items={visibleSliderItems}
        />
    ),
}));

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ isLuigisBoxActive: true }),
}));

vi.mock('graphql/requests/products/queries/RecommendedProductsQuery.generated', () => ({
    useRecommendedProductsQuery: () => mockedUseRecommendedProductsQuery(),
}));

vi.mock('next/router', () => ({
    useRouter: () => ({ pathname: '/category' }),
}));

vi.mock('store/useCookiesStore', () => ({
    useCookiesStore: (selector: (state: { userIdentifier: string }) => string) =>
        selector({ userIdentifier: 'user-identifier' }),
}));

vi.mock('utils/recommender/getRecommenderClientIdentifier', () => ({
    getRecommenderClientIdentifier: () => 'category',
}));

vi.mock('utils/useDeferredRender', () => ({
    useDeferredRender: () => true,
}));

describe('DeferredRecommendedProducts', () => {
    test('renders the basket popup skeleton through the same wrapper and card configuration as products', () => {
        mockedUseRecommendedProductsQuery.mockReturnValue([{ data: undefined, fetching: true }]);

        render(
            <DeferredRecommendedProducts
                recommendationType={TypeRecommendationType.BasketPopup}
                render={(content: ReactElement) => (
                    <section data-testid="recommended-products-wrapper">
                        <h2>Recommended for you</h2>
                        {content}
                    </section>
                )}
            />,
        );

        expect(screen.getByTestId('recommended-products-wrapper')).toContainElement(
            screen.getByTestId('recommended-products-skeleton'),
        );
        expect(screen.getByRole('heading', { name: 'Recommended for you' })).toBeVisible();
        expect(screen.getByTestId('recommended-products-skeleton')).toHaveAttribute('data-heading-hidden', 'true');
        expect(screen.getByTestId('recommended-products-skeleton')).toHaveAttribute('data-size', 'medium');
        expect(screen.getByTestId('recommended-products-skeleton')).toHaveAttribute('data-add-to-cart', 'true');
        expect(screen.getByTestId('recommended-products-skeleton')).toHaveAttribute('data-store-availability', 'true');
        expect(screen.getByTestId('recommended-products-skeleton')).toHaveAttribute('data-variant', 'basketPopup');
        expect(screen.getByTestId('recommended-products-skeleton')).toHaveAttribute('data-visible-slider-items', '4');
    });
});
