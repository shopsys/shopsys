import { handleCartModifications } from 'connectors/cart/Cart';
import { TypeCartModificationsFragment } from 'graphql/requests/cart/fragments/CartModificationsFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { Translate } from 'next-translate';
import { showInfoMessage } from 'utils/toasts/showInfoMessage';
import { describe, expect, test, vi } from 'vitest';

vi.mock('utils/toasts/showInfoMessage', () => ({
    showInfoMessage: vi.fn(),
}));

const t = ((key: string) => key) as Translate;

const cartWithoutModifications: TypeCartModificationsFragment = {
    __typename: 'CartModificationsResult',
    someProductWasRemovedFromEshop: false,
    itemModifications: {
        __typename: 'CartItemModificationsResult',
        noLongerListableCartItems: [],
        cartItemsWithModifiedPrice: [],
        cartItemsWithChangedQuantity: [],
        cartItemsWithRemovedAdditionalServices: [],
        cartItemsWithModifiedAdditionalServicePrices: [],
    },
    transportModifications: {
        __typename: 'CartTransportModificationsResult',
        transportPriceChanged: false,
        transportUnavailable: false,
        transportWeightLimitExceeded: false,
        personalPickupStoreUnavailable: false,
    },
    paymentModifications: {
        __typename: 'CartPaymentModificationsResult',
        paymentPriceChanged: false,
        paymentUnavailable: false,
    },
    promoCodeModifications: {
        __typename: 'CartPromoCodeModificationsResult',
        noLongerApplicablePromoCode: [],
    },
    giftVoucherModifications: {
        __typename: 'CartGiftVoucherModificationsResult',
        noLongerApplicableGiftVouchers: [],
    },
    multipleAddedProductModifications: {
        notAddedProductNames: [],
    },
};

describe('handleCartModifications', () => {
    test('tells the customer to choose another pickup place when the selected one is no longer available', () => {
        const changePaymentInCart = vi.fn();

        handleCartModifications(
            {
                ...cartWithoutModifications,
                transportModifications: {
                    ...cartWithoutModifications.transportModifications,
                    personalPickupStoreUnavailable: true,
                },
            },
            t,
            changePaymentInCart,
        );

        expect(showInfoMessage).toHaveBeenCalledWith(
            'The pickup place you selected is no longer available. Please select another one in the Transport and payment step.',
            GtmMessageOriginType.cart,
        );
        expect(changePaymentInCart).not.toHaveBeenCalled();
    });
});
