import { CartList } from 'components/Pages/Cart/CartList/CartList';
import { CartSummary } from 'components/Pages/Cart/CartSummary/CartSummary';
import { useCurrentCart } from 'connectors/cart/Cart';
import { FC } from 'react';

export const CartContent: FC = () => {
    const { cart } = useCurrentCart();

    return (
        <>
            <CartList items={cart?.items} />
            <CartSummary />
        </>
    );
};
