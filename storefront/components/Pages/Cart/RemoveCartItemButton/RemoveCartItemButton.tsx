import { RemoveCartItemButtonStyled } from './RemoveCartItemButton.style';
import { Icon } from 'components/Basic/Icon/Icon';
import { useRemoveFromCart } from 'hooks/cart/UseRemoveFromCart';
import { FC } from 'react';
import { CartItemType } from 'types/cart';

type RemoveCartItemButtonProps = {
    cartItem: CartItemType;
    listIndex: number;
};

const TEST_IDENTIFIER = 'pages-cart-removecartitembutton';

export const RemoveCartItemButton: FC<RemoveCartItemButtonProps> = ({ cartItem, listIndex }) => {
    const removeItemFromCart = useRemoveFromCart();

    const onRemoveItemFromCartHandler = () => {
        removeItemFromCart(cartItem, listIndex, 'cart');
    };

    return (
        <RemoveCartItemButtonStyled onClick={onRemoveItemFromCartHandler} data-testid={TEST_IDENTIFIER}>
            <Icon iconType="icon" icon="RemoveBold" />
        </RemoveCartItemButtonStyled>
    );
};
