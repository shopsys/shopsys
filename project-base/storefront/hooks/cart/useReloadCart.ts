import { useChangePaymentInCart } from './useChangePaymentInCart';
import { handleCartModifications, useCurrentCart } from 'connectors/cart/Cart';
import { useBroadcastChannel } from 'hooks/useBroadcastChannel';
import useTranslation from 'next-translate/useTranslation';
import { useEffect } from 'react';

export const useReloadCart = (): void => {
    const { modifications, refetchCart } = useCurrentCart(false);
    const [changePaymentInCart] = useChangePaymentInCart();
    const { t } = useTranslation();

    useBroadcastChannel('refetchCart', () => {
        refetchCart();
    });

    useEffect(() => {
        if (modifications) {
            handleCartModifications(modifications, t, changePaymentInCart);
        }
    }, [modifications]);
};
