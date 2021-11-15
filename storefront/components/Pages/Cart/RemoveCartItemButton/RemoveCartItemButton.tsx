import { FC } from 'react';
import Icon from 'components/Basic/Icon';
import { RemoveCartItemButtonStyled } from './RemoveCartItemButton.style';
import { useHandleRemoveFromCart } from 'hooks/cart/UseHandleRemoveFromCart';
import { useRemoveItemFromCart } from 'connectors/cart/Cart';
import { useShopsysSelector } from 'redux/main';

type RemoveCartItemButtonProps = {
    cartItemUuid: string;
};

const RemoveCartItemButton: FC<RemoveCartItemButtonProps> = (props) => {
    const { cartUuid, transport, payment, promoCode } = useShopsysSelector((state) => state.cartInput);
    const [removeItemFromCartResult, removeItemFromCart] = useRemoveItemFromCart();
    useHandleRemoveFromCart(
        removeItemFromCartResult,
        transport?.pickupPlaceIdentifier === undefined ? null : transport.pickupPlaceIdentifier,
        promoCode,
    );

    const onRemoveItemFromCartHanlder = () => {
        if (cartUuid === null) {
            return;
        }

        removeItemFromCart({ cartItemUuid: props.cartItemUuid, cartUuid, transport, payment, promoCode });
    };
    return (
        <RemoveCartItemButtonStyled onClick={onRemoveItemFromCartHanlder}>
            <Icon iconType="icon" icon="RemoveBold" />
        </RemoveCartItemButtonStyled>
    );
};

export default RemoveCartItemButton;
