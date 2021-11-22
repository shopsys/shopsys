import { FC } from 'react';
import Icon from 'components/Basic/Icon';
import { RemoveCartItemButtonStyled } from './RemoveCartItemButton.style';
import { useHandleRemoveFromCart } from 'hooks/cart/UseHandleRemoveFromCart';
import { useRemoveFromCartMutationApi } from 'graphql/generated';
import { useShopsysSelector } from 'redux/main';

type RemoveCartItemButtonProps = {
    cartItemUuid: string;
};

const RemoveCartItemButton: FC<RemoveCartItemButtonProps> = (props) => {
    const { cartUuid, transport, payment, promoCode } = useShopsysSelector((state) => state.cartInput);
    const [removeItemFromCartResult, removeItemFromCart] = useRemoveFromCartMutationApi();
    useHandleRemoveFromCart(
        removeItemFromCartResult,
        transport?.pickupPlaceIdentifier === undefined ? null : transport.pickupPlaceIdentifier,
        promoCode,
    );

    const onRemoveItemFromCartHandler = () => {
        if (cartUuid === null) {
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
