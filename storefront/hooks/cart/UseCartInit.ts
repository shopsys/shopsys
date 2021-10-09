import { loadCart } from 'connectors/cart/Cart';
import { useShopsysSelector } from 'redux/main';

export const useCartInit = (): void => {
    const { cartUuid, transport, payment, promoCode } = useShopsysSelector((state) => state.cookie);
    loadCart(cartUuid, transport, payment, promoCode);
};
