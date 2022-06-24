import { useChangePaymentInCart } from './UseChangePaymentInCart';
import { handleCartModifications, useCurrentCart } from 'connectors/cart/Cart';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useEffect } from 'react';

export const useReloadCart = (): void => {
    const cart = useCurrentCart(false);
    const changePaymentInCart = useChangePaymentInCart();
    const t = useTypedTranslationFunction();

    useEffect(() => {
        if (cart.modifications !== null) {
            handleCartModifications(cart.modifications, t, changePaymentInCart);
        }
    }, [cart.modifications, changePaymentInCart, t]);
};
