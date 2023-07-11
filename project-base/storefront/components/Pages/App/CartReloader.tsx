import { useReloadCart } from 'hooks/cart/useReloadCart';

export const CartReloader: FC = () => {
    useReloadCart();

    return null;
};
