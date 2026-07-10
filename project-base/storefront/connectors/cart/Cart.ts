import { TypeCartGiftVoucherModificationsFragment } from 'graphql/requests/cart/fragments/CartGiftVoucherModificationsFragment.generated';
import { TypeCartItemModificationsFragment } from 'graphql/requests/cart/fragments/CartItemModificationsFragment.generated';
import { TypeCartModificationsFragment } from 'graphql/requests/cart/fragments/CartModificationsFragment.generated';
import { TypeCartPaymentModificationsFragment } from 'graphql/requests/cart/fragments/CartPaymentModificationsFragment.generated';
import { TypeCartPromoCodeModificationsFragment } from 'graphql/requests/cart/fragments/CartPromoCodeModificationsFragment.generated';
import { TypeCartTransportModificationsFragment } from 'graphql/requests/cart/fragments/CartTransportModificationsFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { Translate } from 'next-translate';
import { ChangePaymentInCart } from 'utils/cart/useChangePaymentInCart';
import { showInfoMessage } from 'utils/toasts/showInfoMessage';

export const handleCartModifications = (
    cartModifications: TypeCartModificationsFragment,
    t: Translate,
    changePaymentInCart: ChangePaymentInCart,
): void => {
    const allMessages = [
        ...handleRemovedProductFromEshopModifications(cartModifications.someProductWasRemovedFromEshop, t),
        ...handleCartTransportModifications(cartModifications.transportModifications, t, changePaymentInCart),
        ...handleCartPaymentModifications(cartModifications.paymentModifications, t),
        ...handleCartItemModifications(cartModifications.itemModifications, t),
        ...handleCartPromoCodeModifications(cartModifications.promoCodeModifications, t),
        ...handleCartGiftVoucherModifications(cartModifications.giftVoucherModifications, t),
    ];

    if (allMessages.length > 0) {
        const combinedMessage = allMessages.join(' ');
        showInfoMessage(combinedMessage, GtmMessageOriginType.cart);
    }
};

const handleRemovedProductFromEshopModifications = (
    someProductWasRemovedFromEshop: boolean,
    t: Translate,
): string[] => {
    const messages: string[] = [];

    if (someProductWasRemovedFromEshop) {
        messages.push(t('Some product was removed from e-shop and your cart was recalculated.'));
    }

    return messages;
};

const handleCartTransportModifications = (
    transportModifications: TypeCartTransportModificationsFragment,
    t: Translate,
    changePaymentInCart: ChangePaymentInCart,
): string[] => {
    const messages: string[] = [];

    if (transportModifications.transportPriceChanged) {
        messages.push(t('The price of the transport you selected has changed.'));
    }
    if (transportModifications.transportUnavailable) {
        changePaymentInCart(null, null);
        messages.push(
            t(
                'The transport you selected is no longer available. Your transport and payment selection has been removed.',
            ),
        );
    }
    if (transportModifications.transportWeightLimitExceeded) {
        changePaymentInCart(null, null);
        messages.push(
            t(
                'You have exceeded the weight limit of the selected transport. Your transport and payment selection has been removed.',
            ),
        );
    }

    return messages;
};

const handleCartPaymentModifications = (
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

const handleCartItemModifications = (itemModifications: TypeCartItemModificationsFragment, t: Translate): string[] => {
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

    return messages;
};

const handleCartPromoCodeModifications = (
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

const handleCartGiftVoucherModifications = (
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
