import { FC, useEffect } from 'react';
import { useLoadCart } from 'connectors/cart/Cart';
import { useRouter } from 'next/router';
import { useShopsysSelector } from 'redux/main';

const CartRefresher: FC = () => {
    const router = useRouter();
    const {
        isCartEmpty,
        cartInput: { cartUuid, transport, payment, promoCode },
    } = useShopsysSelector((state) => state.cart);

    const [, refreshCart] = useLoadCart(
        cartUuid,

        transport,
        payment,
        promoCode,
        payment ? payment.goPayBankSwift : null,
    );

    useEffect(() => {
        if (!isCartEmpty) {
            refreshCart();
        }
    }, [router.asPath, isCartEmpty]);

    return null;
};

export default CartRefresher;
