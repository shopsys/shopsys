import { getCartModificationMessages, isPaymentResetRequiredByCartModifications } from 'connectors/cart/Cart';
import { GtmMessageDetailType } from 'gtm/enums/GtmMessageDetailType';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmMessageType } from 'gtm/enums/GtmMessageType';
import { onGtmShowMessageEventHandler } from 'gtm/handlers/onGtmShowMessageEventHandler';
import { useEffect, useEffectEvent } from 'react';
import { CartModificationsBatch } from 'store/slices/createCartModificationsSlice';
import { useSessionStore } from 'store/useSessionStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useChangePaymentInCart } from './useChangePaymentInCart';

/**
 * Runs the effects of every received cart modification exactly once, independently of how many times
 * the cart modifications box renders or whether the customer dismisses it. Batches received before
 * this (deferred) hook mounted are handled when it mounts.
 */
export const useCartModificationsSideEffects = (): void => {
    const { t } = useTranslation();
    const { changePaymentInCart } = useChangePaymentInCart();

    const handleBatch = ({ cartUuid, modifications, hasPayment }: CartModificationsBatch) => {
        const messages = getCartModificationMessages(modifications, t);

        if (messages.length > 0) {
            onGtmShowMessageEventHandler(
                GtmMessageType.information,
                messages.join(' '),
                GtmMessageDetailType.flash_message,
                GtmMessageOriginType.cart,
            );
        }

        if (hasPayment && isPaymentResetRequiredByCartModifications(modifications)) {
            changePaymentInCart(null, null, cartUuid);
        }
    };

    const handleUnhandledBatches = useEffectEvent(() => {
        useSessionStore.getState().takeUnhandledCartModificationsBatches().forEach(handleBatch);
    });

    const subscribeToReceivedBatches = () => {
        handleUnhandledBatches();

        return useSessionStore.subscribe(() => handleUnhandledBatches());
    };

    useEffect(subscribeToReceivedBatches, []);
};
