import { useChangePaymentInCart } from './useChangePaymentInCart';
import { useCurrentCart } from './useCurrentCart';
import { handleCartModifications } from 'connectors/cart/Cart';
import { useEffect, useEffectEvent, useRef } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useBroadcastChannel } from 'utils/useBroadcastChannel';

export const useReloadCart = (): void => {
    const { modifications, fetchCart } = useCurrentCart(false);
    const { changePaymentInCart } = useChangePaymentInCart();
    const { t } = useTranslation();
    const isCartStale = useSessionStore((s) => s.isCartStale);
    const setCartStale = useSessionStore((s) => s.setCartStale);
    const fetchCartRef = useRef(fetchCart);

    useEffect(() => {
        fetchCartRef.current = fetchCart;
    }, [fetchCart]);

    useBroadcastChannel('refetchCart', () => {
        if (document.visibilityState === 'visible') {
            fetchCartRef.current();
        } else {
            setCartStale(true);
        }
    });

    useEffect(() => {
        const handleVisibilityChange = () => {
            if (document.visibilityState === 'visible' && isCartStale) {
                fetchCartRef.current();
                setCartStale(false);
            }
        };

        document.addEventListener('visibilitychange', handleVisibilityChange);

        return () => {
            document.removeEventListener('visibilitychange', handleVisibilityChange);
        };
    }, [isCartStale, setCartStale]);

    const onHandleModifications = useEffectEvent(() => {
        if (modifications) {
            handleCartModifications(modifications, t, changePaymentInCart);
        }
    });

    useEffect(() => {
        if (modifications) {
            onHandleModifications();
        }
    }, [modifications]);
};
