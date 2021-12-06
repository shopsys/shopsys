import { useEffect } from 'react';
import { useLoadCart } from 'connectors/cart/Cart';
import { useRouter } from 'next/router';
import { useShopsysSelector } from 'redux/main';

export const useRefreshCartOnNavigation = (): void => {
    const router = useRouter();
    const { cartUuid, isCartEmpty, transport, payment, promoCode } = useShopsysSelector((state) => state.cartInput);
    const [, refreshCart] = useLoadCart(cartUuid, isCartEmpty, transport, payment, promoCode);

    useEffect(() => {
        if (!isCartEmpty) {
            refreshCart();
        }
    }, [router.asPath, isCartEmpty]);
};
