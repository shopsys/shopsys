import { TypeCartGiftVoucherModificationsFragment } from 'graphql/requests/cart/fragments/CartGiftVoucherModificationsFragment.generated';
import { TypeCartItemModificationsFragment } from 'graphql/requests/cart/fragments/CartItemModificationsFragment.generated';
import { TypeCartModificationsFragment } from 'graphql/requests/cart/fragments/CartModificationsFragment.generated';
import { TypeCartPaymentModificationsFragment } from 'graphql/requests/cart/fragments/CartPaymentModificationsFragment.generated';
import { TypeCartPromoCodeModificationsFragment } from 'graphql/requests/cart/fragments/CartPromoCodeModificationsFragment.generated';
import { TypeCartTransportModificationsFragment } from 'graphql/requests/cart/fragments/CartTransportModificationsFragment.generated';
import { Translate } from 'next-translate';

export const getCartModificationMessages = (
    cartModifications: TypeCartModificationsFragment,
    t: Translate,
): string[] => [
    ...getRemovedProductFromEshopMessages(cartModifications.someProductWasRemovedFromEshop, t),
    ...getCartTransportModificationMessages(cartModifications.transportModifications, t),
    ...getCartPaymentModificationMessages(cartModifications.paymentModifications, t),
    ...getCartItemModificationMessages(cartModifications.itemModifications, t),
    ...getCartPromoCodeModificationMessages(cartModifications.promoCodeModifications, t),
    ...getCartGiftVoucherModificationMessages(cartModifications.giftVoucherModifications, t),
];

export const hasCartModifications = ({
    someProductWasRemovedFromEshop,
    itemModifications,
    transportModifications,
    paymentModifications,
    promoCodeModifications,
    giftVoucherModifications,
}: TypeCartModificationsFragment): boolean => {
    const modifiedItemLists = [
        itemModifications.noLongerListableCartItems,
        itemModifications.cartItemsWithModifiedPrice,
        itemModifications.cartItemsWithChangedQuantity,
        itemModifications.cartItemsWithRemovedAdditionalServices,
        itemModifications.cartItemsWithModifiedAdditionalServicePrices,
        promoCodeModifications.noLongerApplicablePromoCode,
        giftVoucherModifications.noLongerApplicableGiftVouchers,
    ];

    return (
        someProductWasRemovedFromEshop ||
        transportModifications.transportPriceChanged ||
        transportModifications.transportUnavailable ||
        transportModifications.transportWeightLimitExceeded ||
        transportModifications.personalPickupStoreUnavailable ||
        paymentModifications.paymentPriceChanged ||
        paymentModifications.paymentUnavailable ||
        modifiedItemLists.some((modifiedItems) => modifiedItems.length > 0)
    );
};

// The API removes only the transport, so the payment selected for it has to be removed by the storefront
export const isPaymentResetRequiredByCartModifications = ({
    transportModifications,
}: TypeCartModificationsFragment): boolean =>
    transportModifications.transportUnavailable || transportModifications.transportWeightLimitExceeded;

const getRemovedProductFromEshopMessages = (someProductWasRemovedFromEshop: boolean, t: Translate): string[] => {
    const messages: string[] = [];

    if (someProductWasRemovedFromEshop) {
        messages.push(t('Some product was removed from e-shop and your cart was recalculated.'));
    }

    return messages;
};

const getCartTransportModificationMessages = (
    transportModifications: TypeCartTransportModificationsFragment,
    t: Translate,
): string[] => {
    const messages: string[] = [];

    if (transportModifications.transportPriceChanged) {
        messages.push(t('The price of the transport you selected has changed.'));
    }
    if (transportModifications.transportUnavailable) {
        messages.push(
            t(
                'The transport you selected is no longer available. Your transport and payment selection has been removed.',
            ),
        );
    }
    if (transportModifications.transportWeightLimitExceeded) {
        messages.push(
            t(
                'You have exceeded the weight limit of the selected transport. Your transport and payment selection has been removed.',
            ),
        );
    }
    if (transportModifications.personalPickupStoreUnavailable) {
        messages.push(
            t(
                'The pickup place you selected is no longer available. Please select another one in the Transport and payment step.',
            ),
        );
    }

    return messages;
};

const getCartPaymentModificationMessages = (
    paymentModifications: TypeCartPaymentModificationsFragment,
    t: Translate,
): string[] => {
    const messages: string[] = [];

    if (paymentModifications.paymentPriceChanged) {
        messages.push(t('The price of the payment you selected has changed.'));
    }
    if (paymentModifications.paymentUnavailable) {
        messages.push(t('The payment you selected is no longer available.'));
    }

    return messages;
};

const getCartItemModificationMessages = (
    itemModifications: TypeCartItemModificationsFragment,
    t: Translate,
): string[] => {
    const messages: string[] = [];

    for (const cartItemWithModifiedPrice of itemModifications.cartItemsWithModifiedPrice) {
        messages.push(
            t('The price of item {{ itemName }} has changed.', {
                itemName: cartItemWithModifiedPrice.product.fullName,
            }),
        );
    }

    for (const nonListableCartItem of itemModifications.noLongerListableCartItems) {
        messages.push(
            t('Item {{ itemName }} can no longer be bought.', { itemName: nonListableCartItem.product.fullName }),
        );
    }

    for (const itemWithChangedQuantity of itemModifications.cartItemsWithChangedQuantity) {
        messages.push(
            t('Quantity of item {{ itemName }} in cart was changed due to insufficient supply.', {
                itemName: itemWithChangedQuantity.product.fullName,
            }),
        );
    }

    for (const itemWithRemovedAdditionalServices of itemModifications.cartItemsWithRemovedAdditionalServices) {
        messages.push(
            t('Some additional services of item {{ itemName }} are no longer offered and were removed from the cart.', {
                itemName: itemWithRemovedAdditionalServices.product.fullName,
            }),
        );
    }

    for (const itemWithModifiedAdditionalServicePrices of itemModifications.cartItemsWithModifiedAdditionalServicePrices) {
        messages.push(
            t('The price of some additional services of item {{ itemName }} has changed.', {
                itemName: itemWithModifiedAdditionalServicePrices.product.fullName,
            }),
        );
    }

    return messages;
};

const getCartPromoCodeModificationMessages = (
    promoCodeModifications: TypeCartPromoCodeModificationsFragment,
    t: Translate,
): string[] => {
    const messages: string[] = [];

    for (const nonApplicablePromoCode of promoCodeModifications.noLongerApplicablePromoCode) {
        messages.push(
            t('The promo code {{ promoCode }} is no longer applicable.', { promoCode: nonApplicablePromoCode }),
        );
    }

    return messages;
};

const getCartGiftVoucherModificationMessages = (
    giftVoucherModifications: TypeCartGiftVoucherModificationsFragment,
    t: Translate,
): string[] => {
    const messages: string[] = [];

    for (const nonApplicableGiftVoucherCode of giftVoucherModifications.noLongerApplicableGiftVouchers) {
        messages.push(
            t('The gift voucher {{ giftVoucherCode }} is no longer applicable.', {
                giftVoucherCode: nonApplicableGiftVoucherCode,
            }),
        );
    }

    return messages;
};
