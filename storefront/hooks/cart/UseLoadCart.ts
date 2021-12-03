import { loadCart } from 'connectors/cart/Cart';
import { useRefreshCartOnNavigation } from 'hooks/newCart/UseRefreshCartOnNavigation';
import { useShopsysSelector } from 'redux/main';

export const useLoadCart = (): void => {
    const { cartUuid, isCartEmpty, transport, payment, promoCode } = useShopsysSelector((state) => state.cartInput);
    const [, refreshCart] = loadCart(cartUuid, isCartEmpty, transport, payment, promoCode);
    useRefreshCartOnNavigation(refreshCart, isCartEmpty);
};
