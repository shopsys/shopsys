import { loadCart } from 'connectors/cart/Cart';
import { useShopsysSelector } from 'redux/main';

export const useLoadCart = (): void => {
    const { cartUuid, transport, payment, promoCode } = useShopsysSelector((state) => state.cartInput);
    loadCart(cartUuid, transport, payment, promoCode);
};
