import { render, screen } from '@testing-library/react';
import { ProductGift } from 'components/Blocks/Product/ProductGift';
import { TypeProductGiftFragment } from 'graphql/requests/products/fragments/ProductGiftFragment.generated';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Basic/Image/Image', () => ({
    Image: ({ alt }: { alt: string }) => <span aria-label={alt} role="img" />,
}));
vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ defaultLocale: 'en' }),
}));
vi.mock('graphql/requests/settings/queries/SettingsQuery.generated', () => ({
    useSettingsQuery: () => [
        { data: { settings: { pricing: { defaultCurrencyCode: 'EUR', minimumFractionDigits: 2 } } } },
    ],
}));
vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (key: string) => key }),
}));

const createGift = (priceWithVat: string): TypeProductGiftFragment => ({
    uuid: 'gift',
    name: 'Coffee beans',
    fullName: 'Coffee beans 250 g',
    mainCategory: null,
    images: [],
    giftPrice: {
        __typename: 'ProductPrice',
        priceWithVat,
        priceWithoutVat: '1',
        vatAmount: '0.21',
        isPriceFrom: false,
        nextPriceChange: null,
        percentageDiscount: null,
        basicPrice: { priceWithVat: '12.10', priceWithoutVat: '10', vatAmount: '2.10' },
    },
});

describe('ProductGift', () => {
    test.each([
        { scenario: 'paid gift price including VAT', price: '1.21', expected: '€1.21' },
        { scenario: 'free gift', price: '0', expected: 'Free' },
    ])('shows the $scenario alongside the full gift name', ({ price, expected }) => {
        const gift = createGift(price);

        render(<ProductGift gifts={[gift]} />);

        expect(screen.getByText('Coffee beans 250 g')).toBeInTheDocument();
        expect(screen.getAllByText(expected)).toHaveLength(1);
        expect(screen.queryByText('€12.10')).not.toBeInTheDocument();
    });

    test('shows the gift without a price when prices are hidden', () => {
        const gift = createGift('***');

        const { container } = render(<ProductGift gifts={[gift]} />);

        expect(screen.getByText('Coffee beans 250 g')).toBeInTheDocument();
        expect(container).not.toHaveTextContent(/€|Free|NaN|\*\*\*/);
    });
});
