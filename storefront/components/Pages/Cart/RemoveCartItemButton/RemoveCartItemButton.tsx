import { FC } from 'react';
import Icon from 'components/Basic/Icon';
import { RemoveCartItemButtonStyled } from './RemoveCartItemButton.style';
import { useRemoveFromCart } from 'connectors/cart/Cart';
import { useShopsysSelector } from 'redux/main';

type RemoveCartItemButtonProps = {
    cartItemUuid: string;
};

const RemoveCartItemButton: FC<RemoveCartItemButtonProps> = (props) => {
    const testIdentifier = 'pages-cart-removecartitembutton';

    const { cartUuid } = useShopsysSelector((state) => state.user);
    const [, removeItemFromCart] = useRemoveFromCart();

    const onRemoveItemFromCartHandler = () => {
        if (isCartEmpty) {
            return;
        }

        removeItemFromCart({ input: { cartItemUuid: props.cartItemUuid, cartUuid } });
    };

    return (
        <RemoveCartItemButtonStyled onClick={onRemoveItemFromCartHandler} data-testid={testIdentifier}>
            <Icon iconType="icon" icon="RemoveBold" />
        </RemoveCartItemButtonStyled>
    );
};

export default RemoveCartItemButton;
