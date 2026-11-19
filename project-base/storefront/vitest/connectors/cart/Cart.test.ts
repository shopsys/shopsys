import {
    getCartModificationMessages,
    hasCartModifications,
    isPaymentResetRequiredByCartModifications,
} from 'connectors/cart/Cart';
import { Translate } from 'next-translate';
import { describe, expect, test } from 'vitest';
import { getCartModifications } from 'vitest/helpers/cartModificationsFixture';

const t = ((key: string) => key) as Translate;

describe('cart modifications', () => {
    test('tells the customer to choose another pickup place when the selected one is no longer available', () => {
        const modifications = getCartModifications({
            transportModifications: { personalPickupStoreUnavailable: true },
        });

        expect(getCartModificationMessages(modifications, t)).toEqual([
            'The pickup place you selected is no longer available. Please select another one in the Transport and payment step.',
        ]);
        expect(isPaymentResetRequiredByCartModifications(modifications)).toBe(false);
    });

    test('treats a response without any modification as unmodified', () => {
        expect(hasCartModifications(getCartModifications())).toBe(false);
    });

    test.each([
        ['a removed product', getCartModifications({ someProductWasRemovedFromEshop: true })],
        ['a changed payment price', getCartModifications({ paymentModifications: { paymentPriceChanged: true } })],
        [
            'an inapplicable promo code',
            getCartModifications({ promoCodeModifications: { noLongerApplicablePromoCode: ['SUMMER'] } }),
        ],
    ])('treats a response with %s as modified', (_, modifications) => {
        expect(hasCartModifications(modifications)).toBe(true);
    });

    test.each([
        ['an unavailable transport', { transportUnavailable: true }],
        ['an exceeded transport weight limit', { transportWeightLimitExceeded: true }],
    ])('requires a payment reset after %s', (_, transportModifications) => {
        expect(isPaymentResetRequiredByCartModifications(getCartModifications({ transportModifications }))).toBe(true);
    });
});
