import {
    ListItemDetailStyled,
    ListItemImageWrapperStyled,
    ListItemPriceStyled,
    ListItemQuantityStyled,
    ListItemRemoveButtonStyled,
    ListItemStyled,
    ListItemTitleStyled,
} from './ListItem.style';
import { CartItemType } from 'connectors/cart/types';
import { FC } from 'react';
import { formatPrice } from 'utils/formatting';
import Icon from 'components/Basic/Icon';
import Image from 'components/Basic/Image';
import NextLink from 'next/link';
import { useHandleRemoveFromCart } from 'hooks/cart/UseHandleRemoveFromCart';
import { useRemoveItemFromCart } from 'connectors/cart/Cart';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type ListItemProps = {
    cartItem: CartItemType;
};

const ListItem: FC<ListItemProps> = (props) => {
    const t = useTypedTranslationFunction();
    const domainConfig = useShopsysSelector((state) => state.domain);
    const { cartUuid, transport, payment, promoCode } = useShopsysSelector((state) => state.cartInput);

    const [removeItemFromCartResult, removeItemFromCart] = useRemoveItemFromCart();
    useHandleRemoveFromCart(
        removeItemFromCartResult,
        transport?.personalPickupStoreUuid === undefined ? null : transport.personalPickupStoreUuid,
        promoCode,
    );

    const onRemoveItemFromCartHanlder = () => {
        if (cartUuid === null) {
            return;
        }

        removeItemFromCart({ cartItemUuid: props.cartItem.uuid, cartUuid, transport, payment, promoCode });
    };

    return (
        <ListItemStyled key={props.cartItem.uuid}>
            <ListItemImageWrapperStyled>
                <Image alt={props.cartItem.product.fullName} image={props.cartItem.product.image} />
            </ListItemImageWrapperStyled>
            <ListItemDetailStyled>
                <NextLink href={props.cartItem.product.slug}>
                    <ListItemTitleStyled>{props.cartItem.product.fullName}</ListItemTitleStyled>
                </NextLink>
                <ListItemQuantityStyled>
                    {props.cartItem.quantity + props.cartItem.product.unit.name}
                </ListItemQuantityStyled>
                <ListItemPriceStyled>
                    {formatPrice(
                        props.cartItem.product.price.priceWithVat * props.cartItem.quantity,
                        domainConfig.currencyCode,
                        t,
                    )}
                </ListItemPriceStyled>
            </ListItemDetailStyled>
            <ListItemRemoveButtonStyled onClick={onRemoveItemFromCartHanlder}>
                <Icon icon="RemoveBold" />
            </ListItemRemoveButtonStyled>
        </ListItemStyled>
    );
};

export default ListItem;
