import { act, fireEvent, render, screen, waitFor, within } from '@testing-library/react';
import { CartListItem } from 'components/Pages/Cart/CartList/CartListItem';
import { TypeCartItemTypeEnum } from 'graphql/types';
import type React from 'react';
import { describe, expect, test, vi } from 'vitest';

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

vi.mock('components/Basic/GiftBadge/GiftBadge', () => ({
    GiftBadge: () => <span>Gift</span>,
}));

vi.mock('components/Basic/Icon/CloseIcon', () => ({
    CloseIcon: () => null,
}));

vi.mock('components/Basic/Image/Image', () => ({
    Image: ({ alt }: { alt: string }) => <span aria-label={alt} role="img" />,
}));

vi.mock('components/Blocks/Product/ProductAvailability', () => ({
    ProductAvailability: ({ availability }: { availability: { name: string } }) => <span>{availability.name}</span>,
}));

vi.mock('components/Forms/Button/IconButton', () => ({
    IconButton: ({ ariaLabel, onClick }: { ariaLabel: string; onClick: () => void }) => (
        <button aria-label={ariaLabel} type="button" onClick={onClick} />
    ),
}));

vi.mock('components/Forms/Spinbox/Spinbox', async () => {
    const { forwardRef } = await vi.importActual<typeof import('react')>('react');

    return {
        Spinbox: forwardRef<
            HTMLInputElement,
            {
                defaultValue: number;
                inputAriaLabel: string;
                onChangeValueCallback: (quantity: number) => void;
            }
        >(({ defaultValue, inputAriaLabel, onChangeValueCallback }, ref) => (
            <input
                ref={ref}
                aria-label={inputAriaLabel}
                defaultValue={defaultValue}
                type="number"
                onChange={(event) => onChangeValueCallback(event.currentTarget.valueAsNumber)}
            />
        )),
    };
});

vi.mock('components/Pages/Cart/CartItemPrice', () => ({
    CartItemPrice: () => <span>Cart item price</span>,
}));

vi.mock('utils/formatting/useFormatPrice', () => ({
    useFormatPrice: () => (price: string | number) => `€${price}`,
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string, options?: Record<string, string | number>) =>
            Object.entries(options ?? {}).reduce(
                (translatedKey, [optionKey, optionValue]) =>
                    translatedKey.replaceAll(`{{ ${optionKey} }}`, String(optionValue)),
                key,
            ),
    }),
}));

vi.mock('utils/useDebounce', () => ({
    useDebounce: (value: unknown) => value,
}));

describe('CartListItem', () => {
    const item = {
        freeQuantity: 1,
        product: {
            __typename: 'RegularProduct',
            availability: {
                name: 'In stock',
            },
            availableStoresCount: 1,
            catalogNumber: 'ABC123',
            categories: [{ name: 'TV, audio' }],
            fullName: '32" Philips TV',
            giftPrice: {
                priceWithVat: '0',
                priceWithoutVat: '0',
            },
            isAllowedNegativeStock: false,
            isInquiryType: false,
            mainImage: {
                url: '/image.jpg',
            },
            price: {
                priceWithVat: '10',
                priceWithoutVat: '8',
            },
            promotionBuyQuantity: null,
            promotionFreeQuantity: null,
            slug: '/32-philips-tv',
            stockQuantity: 10,
            unit: {
                name: 'pcs',
            },
            uuid: 'product-uuid',
        },
        quantity: 3,
        type: TypeCartItemTypeEnum.Product,
        uuid: 'cart-item-uuid',
    };
    const createAddToCartResult = (quantity: number) => ({ addProductResult: { cartItem: { quantity } } }) as any;

    test('uses one product detail link for the image and product name only', () => {
        render(
            <CartListItem
                isRemovingFromCart={false}
                item={item as any}
                listIndex={0}
                onAddToCart={vi.fn()}
                onRemoveFromCart={vi.fn()}
            />,
        );

        const productLinks = screen.getAllByRole('link');

        expect(productLinks).toHaveLength(1);
        expect(productLinks[0]).toHaveAccessibleName('Go to product page of 32" Philips TV');
        expect(within(productLinks[0]).getByRole('img')).toBeInTheDocument();
        expect(within(productLinks[0]).getByText('32" Philips TV')).toBeInTheDocument();
        expect(within(productLinks[0]).queryByText('Code: ABC123')).not.toBeInTheDocument();
        expect(within(productLinks[0]).queryByText('In stock')).not.toBeInTheDocument();
    });

    test('adds screen reader summary with code, availability, quantity, unit price and totals', () => {
        render(
            <CartListItem
                isRemovingFromCart={false}
                item={item as any}
                listIndex={0}
                onAddToCart={vi.fn()}
                onRemoveFromCart={vi.fn()}
            />,
        );

        expect(screen.getByRole('region', { name: '32" Philips TV' })).toHaveAccessibleDescription(
            'Code: ABC123. Availability: In stock. Quantity: 3 pcs. Unit price with VAT: €10. Item total with VAT: €20. Item total without VAT: €16. 1 pcs is free.',
        );
    });

    test('restores the last server-confirmed quantity when a queued update fails', async () => {
        let resolveFirstUpdate: (value: any) => void = () => undefined;
        const firstUpdate = new Promise<any>((resolve) => {
            resolveFirstUpdate = resolve;
        });
        const onAddToCart = vi.fn().mockReturnValueOnce(firstUpdate).mockResolvedValueOnce(null);

        render(
            <CartListItem
                isRemovingFromCart={false}
                item={{ ...item, freeQuantity: 0, quantity: 1 } as any}
                listIndex={0}
                onAddToCart={onAddToCart}
                onRemoveFromCart={vi.fn()}
            />,
        );
        const quantityInput = screen.getByRole('spinbutton', { name: 'Quantity of 32" Philips TV' });

        fireEvent.change(quantityInput, { target: { value: '2' } });
        fireEvent.change(quantityInput, { target: { value: '3' } });
        await act(async () => {
            resolveFirstUpdate(createAddToCartResult(2));
            await firstUpdate;
        });

        await waitFor(() => {
            expect(onAddToCart).toHaveBeenNthCalledWith(1, 'product-uuid', 2, 0, true);
            expect(onAddToCart).toHaveBeenNthCalledWith(2, 'product-uuid', 3, 0, true);
            expect(quantityInput).toHaveValue(2);
        });
    });

    test('syncs the spinbox to a server-adjusted quantity when no update is queued', async () => {
        const onAddToCart = vi.fn().mockResolvedValue(createAddToCartResult(2));

        render(
            <CartListItem
                isRemovingFromCart={false}
                item={{ ...item, freeQuantity: 0, quantity: 1 } as any}
                listIndex={0}
                onAddToCart={onAddToCart}
                onRemoveFromCart={vi.fn()}
            />,
        );
        const quantityInput = screen.getByRole('spinbutton', { name: 'Quantity of 32" Philips TV' });

        fireEvent.change(quantityInput, { target: { value: '3' } });

        await waitFor(() => {
            expect(onAddToCart).toHaveBeenCalledWith('product-uuid', 3, 0, true);
            expect(quantityInput).toHaveValue(2);
        });
    });
});
