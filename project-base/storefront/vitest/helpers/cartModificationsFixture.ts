import { TypeCartModificationsFragment } from 'graphql/requests/cart/fragments/CartModificationsFragment.generated';

type CartModificationsOverrides = {
    transportModifications?: Partial<TypeCartModificationsFragment['transportModifications']>;
    paymentModifications?: Partial<TypeCartModificationsFragment['paymentModifications']>;
    promoCodeModifications?: Partial<TypeCartModificationsFragment['promoCodeModifications']>;
    someProductWasRemovedFromEshop?: boolean;
};

export const getCartModifications = ({
    transportModifications,
    paymentModifications,
    promoCodeModifications,
    someProductWasRemovedFromEshop = false,
}: CartModificationsOverrides = {}): TypeCartModificationsFragment => ({
    __typename: 'CartModificationsResult',
    someProductWasRemovedFromEshop,
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
        ...transportModifications,
    },
    paymentModifications: {
        __typename: 'CartPaymentModificationsResult',
        paymentPriceChanged: false,
        paymentUnavailable: false,
        ...paymentModifications,
    },
    promoCodeModifications: {
        __typename: 'CartPromoCodeModificationsResult',
        noLongerApplicablePromoCode: [],
        ...promoCodeModifications,
    },
    giftVoucherModifications: {
        __typename: 'CartGiftVoucherModificationsResult',
        noLongerApplicableGiftVouchers: [],
    },
    multipleAddedProductModifications: {
        notAddedProductNames: [],
    },
});

const price = { __typename: 'Price', priceWithVat: '0', priceWithoutVat: '0', vatAmount: '0' };

export const paymentResponseFixture = {
    __typename: 'Payment',
    uuid: 'cash-on-delivery',
    name: 'Cash on delivery',
    description: null,
    instructions: null,
    price,
    mainImage: null,
    type: 'basic',
    goPayPaymentMethod: null,
};

type CartResponseOptions = {
    payment?: object | null;
    uuid?: string | null;
};

// A network response of CartFragment complete enough for the production Graphcache
export const getCartResponse = (
    modifications: TypeCartModificationsFragment,
    { payment = null, uuid = 'cart-1' }: CartResponseOptions = {},
) => ({
    __typename: 'Cart',
    uuid,
    items: [],
    totalPrice: price,
    totalItemsPrice: price,
    totalItemsPriceBeforeDiscount: price,
    totalProductPriceAdjustmentsDiscount: price,
    totalDiscountPrice: price,
    modifications: {
        ...modifications,
        multipleAddedProductModifications: {
            __typename: 'CartMultipleAddedProductModificationsResult',
            notAddedProductNames: [],
        },
    },
    remainingAmountForFreeTransport: null,
    transport: null,
    payment,
    promoCodes: [],
    giftVouchers: [],
    remainingAmountToPay: '0',
    remainingItemsAmountToPay: '0',
    isNothingLeftToPay: false,
    giftVouchersExceedPayableAmount: false,
    selectedPickupPlaceIdentifier: null,
    paymentGoPayBankSwift: null,
    roundingPrice: null,
});
