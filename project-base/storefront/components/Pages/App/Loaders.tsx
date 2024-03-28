import { useAuthLoader } from 'hooks/app/useAuthLoader';
import { usePageLoader } from 'hooks/app/usePageLoader';
import { useReloadCart } from 'hooks/cart/useReloadCart';

export const Loaders = () => {
    useAuthLoader();
    usePageLoader();
    useReloadCart();

    return null;
};
