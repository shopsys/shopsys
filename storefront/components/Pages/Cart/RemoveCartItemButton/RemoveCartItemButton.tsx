import { RemoveCartItemButtonStyled } from './RemoveCartItemButton.style';
import { Icon } from 'components/Basic/Icon/Icon';
import { useRemoveFromCart } from 'hooks/cart/UseRemoveFromCart';
import { FC } from 'react';
import { CartItemType } from 'types/cart';

type RemoveCartItemButtonProps = {
    cartItem: CartItemType;
    listIndex: number;
};

export const RemoveCartItemButton: FC<RemoveCartItemButtonProps> = (props) => {
    const testIdentifier = 'pages-cart-removecartitembutton';

    const removeItemFromCart = useRemoveFromCart();

    const onRemoveItemFromCartHandler = () => {
        removeItemFromCart(props.cartItem, props.listIndex, 'cart');
    };

    return (
        <RemoveCartItemButtonStyled onClick={onRemoveItemFromCartHandler} data-testid={testIdentifier}>
            <Icon iconType="icon" icon="RemoveBold" />
        </RemoveCartItemButtonStyled>
    );
};
