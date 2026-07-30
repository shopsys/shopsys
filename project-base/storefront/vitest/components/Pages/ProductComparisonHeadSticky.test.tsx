import { act, render, screen, within } from '@testing-library/react';
import {
    PRODUCT_COMPARISON_END_TRIGGER_ID,
    PRODUCT_COMPARISON_STICKY_TRIGGER_ID,
} from 'components/Pages/ProductComparison/ProductComparisonHead';
import { ProductComparisonHeadSticky } from 'components/Pages/ProductComparison/ProductComparisonHeadSticky';
import { TypeProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import type React from 'react';
import { useScrollTop } from 'utils/ui/useScrollTop';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const scrollCallbacks = vi.hoisted(() => new Map<string, (isPastElement: boolean) => void>());

vi.mock('components/Basic/ExtendedNextLink/ExtendedNextLink', () => ({
    ExtendedNextLink: ({
        children,
        href,
        'aria-label': ariaLabel,
    }: {
        children: React.ReactNode;
        href: string;
        'aria-label'?: string;
    }) => (
        <a aria-label={ariaLabel} href={href}>
            {children}
        </a>
    ),
}));

vi.mock('components/Basic/Image/Image', () => ({
    Image: () => <span role="img" />,
}));

vi.mock('components/Layout/Webline/Webline', () => ({
    Webline: ({ children }: { children: React.ReactNode }) => <div>{children}</div>,
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string, options?: Record<string, string>) =>
            Object.entries(options ?? {}).reduce(
                (translatedKey, [optionKey, optionValue]) =>
                    translatedKey.replaceAll(`{{ ${optionKey} }}`, optionValue),
                key,
            ),
    }),
}));

vi.mock('utils/ui/useScrollTop', () => ({
    useScrollTop: vi.fn((elementId: string, callback: (isPastElement: boolean) => void) => {
        scrollCallbacks.set(elementId, callback);
    }),
}));

describe('ProductComparisonHeadSticky', () => {
    const product = {
        fullName: '32" Philips TV',
        mainImage: { url: '/image.jpg' },
        slug: '/32-philips-tv',
        uuid: 'product-uuid',
    } as TypeProductInProductListFragment;

    beforeEach(() => {
        vi.clearAllMocks();
        scrollCallbacks.clear();
    });

    test('respects the fixed storefront header offset', () => {
        const { container } = render(
            <ProductComparisonHeadSticky comparedProducts={[]} tableFirstColumnWidth={256} tableMarginLeft={0} />,
        );

        expect(container.firstElementChild).toHaveClass('top-(--sticky-navigation-offset,0px)', 'z-menu');
    });

    test('is visible only between the first product name and the end of the table', () => {
        const { container } = render(
            <ProductComparisonHeadSticky comparedProducts={[]} tableFirstColumnWidth={256} tableMarginLeft={0} />,
        );

        expect(container.firstElementChild).toHaveClass(
            'flex',
            'invisible',
            '-translate-y-full',
            'opacity-0',
            'pointer-events-none',
            'transition-[transform,opacity,visibility]',
            'duration-300',
        );
        expect(container.firstElementChild).toHaveAttribute('aria-hidden', 'true');

        expect(useScrollTop).toHaveBeenCalledWith(PRODUCT_COMPARISON_STICKY_TRIGGER_ID, expect.any(Function));
        expect(useScrollTop).toHaveBeenCalledWith(PRODUCT_COMPARISON_END_TRIGGER_ID, expect.any(Function));

        act(() => scrollCallbacks.get(PRODUCT_COMPARISON_STICKY_TRIGGER_ID)?.(true));

        expect(container.firstElementChild).toHaveClass('visible', 'translate-y-0', 'opacity-100');
        expect(container.firstElementChild).toHaveAttribute('aria-hidden', 'false');

        act(() => scrollCallbacks.get(PRODUCT_COMPARISON_END_TRIGGER_ID)?.(true));

        expect(container.firstElementChild).toHaveClass(
            'invisible',
            '-translate-y-full',
            'opacity-0',
            'pointer-events-none',
        );
        expect(container.firstElementChild).toHaveAttribute('aria-hidden', 'true');
    });

    test('uses one product detail link for the image and product name', () => {
        render(
            <ProductComparisonHeadSticky
                comparedProducts={[product]}
                tableFirstColumnWidth={256}
                tableMarginLeft={0}
            />,
        );

        act(() => scrollCallbacks.get(PRODUCT_COMPARISON_STICKY_TRIGGER_ID)?.(true));

        const productLinks = screen.getAllByRole('link');

        expect(productLinks).toHaveLength(1);
        expect(productLinks[0]).toHaveAccessibleName('Go to product page of 32" Philips TV');
        expect(within(productLinks[0]).getByRole('img')).toBeInTheDocument();
        expect(within(productLinks[0]).getByText('32" Philips TV')).toBeInTheDocument();
    });

    test('uses the measured table first column width', () => {
        const { container } = render(
            <ProductComparisonHeadSticky comparedProducts={[]} tableFirstColumnWidth={256} tableMarginLeft={0} />,
        );

        const firstColumn = container.firstElementChild?.firstElementChild?.firstElementChild;

        expect(firstColumn).toHaveStyle({ width: '256px', minWidth: '256px', maxWidth: '256px' });
    });
});
