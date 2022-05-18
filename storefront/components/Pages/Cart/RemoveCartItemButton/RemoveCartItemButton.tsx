import { RemoveCartItemButtonStyled } from './RemoveCartItemButton.style';
import Icon from 'components/Basic/Icon';
import { useRemoveFromCart } from 'hooks/cart/UseRemoveFromCart';
import { FC } from 'react';

type RemoveCartItemButtonProps = {
    cartItemUuid: string;
};

const RemoveCartItemButton: FC<RemoveCartItemButtonProps> = (props) => {
    const testIdentifier = 'pages-cart-removecartitembutton';

    const removeItemFromCart = useRemoveFromCart();

    const onRemoveItemFromCartHandler = () => {
        removeItemFromCart(props.cartItemUuid);
    };

    return (
        <RemoveCartItemButtonStyled onClick={onRemoveItemFromCartHandler} data-testid={testIdentifier}>
            <Icon iconType="icon" icon="RemoveBold" />
        </RemoveCartItemButtonStyled>
    );
};

export default RemoveCartItemButton;
