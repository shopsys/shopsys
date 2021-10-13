import { FC, useRef } from 'react';
import {
    ImageCellStyled,
    ImageWrapperStyled,
    InfoCellStyled,
    ItemPriceCellStyled,
    ItemPriceStyled,
    ItemStyled,
    RemoveButtonCellStyled,
    RemoveButtonStyled,
    SpinboxCellStyled,
    TotalPriceCellStyled,
    TotalPriceStyled,
} from './Item.style';
import { useChangeCartItemQuantity, useRemoveItemFromCart } from 'connectors/cart/Cart';
import { CartItemType } from 'connectors/cart/types';
import { formatPrice } from 'utils/formatting';
import Icon from 'components/Basic/Icon';
import Image from 'components/Basic/Image';
import ItemInfo from './ItemInfo';
import NextLink from 'next/link';
import Spinbox from 'components/Forms/Spinbox';
import { useHandleAddToCart } from 'hooks/cart/UseHandleAddToCart';
import { useHandleRemoveFromCart } from 'hooks/cart/UseHandleRemoveFromCart';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type ItemProps = {
    item: CartItemType;
};

const Item: FC<ItemProps> = (props) => {
    const timeoutRef = useRef<NodeJS.Timeout | null>(null);
    const spinboxRef = useRef<HTMLInputElement | null>(null);
    const t = useTypedTranslationFunction();
    const { cartUuid, transport, payment, promoCode } = useShopsysSelector((state) => state.cookie);
    const [removeItemFromCartResult, removeItemFromCart] = useRemoveItemFromCart();
    const [changeCartItemQuantityResult, changeCartItemQuantity] = useChangeCartItemQuantity();
    useHandleAddToCart(
        changeCartItemQuantityResult,
        transport?.personalPickupStoreUuid === undefined ? null : transport.personalPickupStoreUuid,
        promoCode,
    );
    useHandleRemoveFromCart(
        removeItemFromCartResult,
        transport?.personalPickupStoreUuid === undefined ? null : transport.personalPickupStoreUuid,
        promoCode,
    );

    const onChangeValueHandler = () => {
        if (timeoutRef.current === null) {
            timeoutRef.current = setUpdateTimeout();
        } else {
            clearTimeout(timeoutRef.current);
            timeoutRef.current = setUpdateTimeout();
        }
    };

    const onRemoveItemFromCartHanlder = () => {
        if (cartUuid === null) {
            return;
        }

        removeItemFromCart({ cartItemUuid: props.item.uuid, cartUuid, transport, payment, promoCode });
    };

    const setUpdateTimeout = () => {
        if (cartUuid === undefined && spinboxRef.current !== null) {
            return null;
        }

        return setTimeout(() => {
            changeCartItemQuantity({
                productUuid: props.item.product.uuid,
                cartUuid: cartUuid!,
                quantity: spinboxRef.current!.valueAsNumber,
                isAbsoluteQuantity: true,
                transport,
                payment,
                promoCode,
            });
        }, 500);
    };

    return (
        <ItemStyled>
            <ImageCellStyled>
                <NextLink href={props.item.product.slug} passHref>
                    <ImageWrapperStyled>
                        <Image image={props.item.product.image} alt={props.item.product.fullName} />
                    </ImageWrapperStyled>
                </NextLink>
            </ImageCellStyled>
            <InfoCellStyled>
                <ItemInfo item={props.item} />
            </InfoCellStyled>
            <SpinboxCellStyled>
                <Spinbox
                    min={1}
                    max={props.item.product.stockQuantity}
                    step={1}
                    defaultValue={props.item.quantity}
                    ref={spinboxRef}
                    onChangeValueCallback={onChangeValueHandler}
                />
            </SpinboxCellStyled>
            <ItemPriceCellStyled>
                <ItemPriceStyled>
                    {formatPrice(props.item.product.price.priceWithVat, props.item.product.price.currencyCode, t) +
                        '\u00A0/\u00A0' +
                        t('pc')}
                </ItemPriceStyled>
            </ItemPriceCellStyled>
            <TotalPriceCellStyled>
                <TotalPriceStyled>
                    {formatPrice(
                        props.item.product.price.priceWithVat * props.item.quantity,
                        props.item.product.price.currencyCode,
                        t,
                    )}
                </TotalPriceStyled>
            </TotalPriceCellStyled>
            <RemoveButtonCellStyled>
                <RemoveButtonStyled onClick={onRemoveItemFromCartHanlder}>
                    <Icon icon="RemoveBold" />
                </RemoveButtonStyled>
            </RemoveButtonCellStyled>
        </ItemStyled>
    );
};

export default Item;
