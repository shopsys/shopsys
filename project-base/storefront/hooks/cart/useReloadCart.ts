import { useChangePaymentInCart } from './useChangePaymentInCart';
import { useCurrentCart } from './useCurrentCart';
import { handleCartModifications } from 'connectors/cart/Cart';
import { useBroadcastChannel } from 'hooks/useBroadcastChannel';
import useTranslation from 'next-translate/useTranslation';
import { useEffect } from 'react';

export const useReloadCart = (): void => {
    const { modifications, fetchCart } = useCurrentCart(false);
    const [changePaymentInCart] = useChangePaymentInCart();
    const { t } = useTranslation();

    useBroadcastChannel('refetchCart', () => {
        fetchCart();
    });

    useEffect(() => {
        if (modifications) {
            handleCartModifications(modifications, t, changePaymentInCart);
        }
    }, [modifications]);
};
