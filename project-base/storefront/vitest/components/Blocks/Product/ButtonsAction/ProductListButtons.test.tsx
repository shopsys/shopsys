import { fireEvent, screen } from '@testing-library/react';
import { ProductCompareButton } from 'components/Blocks/Product/ButtonsAction/ProductCompareButton';
import { ProductWishlistButton } from 'components/Blocks/Product/ButtonsAction/ProductWishlistButton';
import { ReactElement } from 'react';
import { describe, expect, test, vi } from 'vitest';
import { renderWithTooltipProvider as render } from 'vitest/helpers/renderWithTooltipProvider';

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string) => key,
    }),
}));

const buttonScenarios: { button: ReactElement; tooltipLabel: string }[] = [
    {
        button: (
            <ProductCompareButton
                isProductInComparison={false}
                productName="Test product"
                toggleProductInComparison={vi.fn()}
            />
        ),
        tooltipLabel: 'Add product to comparison',
    },
    {
        button: (
            <ProductWishlistButton
                isProductInWishlist={false}
                productName="Test product"
                toggleProductInWishlist={vi.fn()}
            />
        ),
        tooltipLabel: 'Add product to wishlist',
    },
    {
        button: (
            <ProductCompareButton
                isProductInComparison
                productName="Test product"
                toggleProductInComparison={vi.fn()}
            />
        ),
        tooltipLabel: 'Remove product from comparison',
    },
    {
        button: (
            <ProductWishlistButton isProductInWishlist productName="Test product" toggleProductInWishlist={vi.fn()} />
        ),
        tooltipLabel: 'Remove product from wishlist',
    },
];

const activeButtonScenarios: { button: ReactElement; name: string }[] = [
    {
        button: (
            <ProductCompareButton
                isProductInComparison
                productName="Test product"
                toggleProductInComparison={vi.fn()}
            />
        ),
        name: 'icon-only comparison',
    },
    {
        button: (
            <ProductCompareButton
                isWithText
                isProductInComparison
                productName="Test product"
                toggleProductInComparison={vi.fn()}
            />
        ),
        name: 'comparison with text',
    },
    {
        button: (
            <ProductWishlistButton isProductInWishlist productName="Test product" toggleProductInWishlist={vi.fn()} />
        ),
        name: 'icon-only wishlist',
    },
    {
        button: (
            <ProductWishlistButton
                isWithText
                isProductInWishlist
                productName="Test product"
                toggleProductInWishlist={vi.fn()}
            />
        ),
        name: 'wishlist with text',
    },
];

describe('Product list action buttons', () => {
    test.each(buttonScenarios)('shows the $tooltipLabel tooltip for an icon-only action', ({
        button,
        tooltipLabel,
    }) => {
        render(button);
        const trigger = screen.getByRole('button');

        fireEvent.focus(trigger);

        expect(trigger).not.toHaveAttribute('title');
        expect(screen.getByRole('tooltip')).toHaveTextContent(tooltipLabel);
    });

    test('does not add a custom tooltip when the action has visible text', () => {
        render(
            <ProductCompareButton
                isWithText
                isProductInComparison={false}
                productName="Test product"
                toggleProductInComparison={vi.fn()}
            />,
        );
        const trigger = screen.getByRole('button');

        fireEvent.focus(trigger);

        expect(trigger).toHaveAttribute('title', 'Add product to comparison');
        expect(screen.queryByRole('tooltip')).not.toBeInTheDocument();
    });

    test.each(activeButtonScenarios)('uses the active accent color for $name', ({ button }) => {
        render(button);

        expect(screen.getByRole('button').querySelector('svg')).toHaveClass('text-icon-accent-red');
    });

    test.each([
        { isProductInComparison: false, isWithText: false },
        { isProductInComparison: true, isWithText: false },
        { isProductInComparison: false, isWithText: true },
        { isProductInComparison: true, isWithText: true },
    ])('does not announce a dialog when isProductInComparison is $isProductInComparison and isWithText is $isWithText', ({
        isProductInComparison,
        isWithText,
    }) => {
        render(
            <ProductCompareButton
                isProductInComparison={isProductInComparison}
                isWithText={isWithText}
                productName="Test product"
                toggleProductInComparison={vi.fn()}
            />,
        );

        expect(screen.getByRole('button')).not.toHaveAttribute('aria-haspopup');
    });
});
