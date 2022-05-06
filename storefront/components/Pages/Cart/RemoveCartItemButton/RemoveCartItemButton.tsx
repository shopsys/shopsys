import { FC } from 'react';
import Icon from 'components/Basic/Icon';
import { RemoveCartItemButtonStyled } from './RemoveCartItemButton.style';
import { useRemoveFromCart } from 'hooks/cart/UseRemoveFromCart';

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
