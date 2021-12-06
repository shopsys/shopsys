import { FC } from 'react';
import Icon from 'components/Basic/Icon';
import { RemoveCartItemButtonStyled } from './RemoveCartItemButton.style';
import { useRemoveFromCart } from 'connectors/cart/Cart';
import { useShopsysSelector } from 'redux/main';

type RemoveCartItemButtonProps = {
    cartItemUuid: string;
};

const RemoveCartItemButton: FC<RemoveCartItemButtonProps> = (props) => {
    const { cartUuid, isCartEmpty, transport, payment, promoCode } = useShopsysSelector((state) => state.cartInput);
    const [, removeItemFromCart] = useRemoveFromCart();

    const onRemoveItemFromCartHandler = () => {
        if (isCartEmpty) {
            return;
        }

        removeItemFromCart({ cartItemUuid: props.cartItemUuid, cartUuid, transport, payment, promoCode });
    };

    return (
        <RemoveCartItemButtonStyled onClick={onRemoveItemFromCartHandler}>
            <Icon iconType="icon" icon="RemoveBold" />
        </RemoveCartItemButtonStyled>
    );
};

export default RemoveCartItemButton;
