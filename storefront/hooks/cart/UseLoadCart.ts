import { loadCart } from 'connectors/cart/Cart';
import { useEffect } from 'react';
import { useRouter } from 'next/router';
import { useShopsysSelector } from 'redux/main';

export const useLoadCart = (): void => {
    const router = useRouter();
    const { cartUuid, transport, payment, promoCode } = useShopsysSelector((state) => state.cartInput);
    const [, refreshCart] = loadCart(cartUuid, transport, payment, promoCode);
    useEffect(() => {
        refreshCart();
    }, [router.asPath]);
};
