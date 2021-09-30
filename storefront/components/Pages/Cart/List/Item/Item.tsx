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
import { mapCart, useChangeCartItemQuantity, useRemoveItemFromCart } from 'connectors/cart/Cart';
import { useShopsysDispatch, useShopsysSelector } from 'redux/store';
import { CartItemType } from 'connectors/cart/types';
import { formatPrice } from 'utils/formatting';
import Icon from 'components/Basic/Icon';
import Image from 'components/Basic/Image';
import ItemInfo from './ItemInfo';
import NextLink from 'next/link';
import { showErrorMessage } from 'components/Helpers/Toasts';
import Spinbox from 'components/Forms/Spinbox';
import { updateUserDataCookie } from 'helpers/Cookies';
import { userActions } from 'redux/store/UserStore';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type ItemProps = {
    item: CartItemType;
};

const Item: FC<ItemProps> = (props) => {
    const timeoutRef = useRef<NodeJS.Timeout | null>(null);
    const spinboxRef = useRef<HTMLInputElement | null>(null);
    const t = useTypedTranslationFunction();
    const cartUuid = useShopsysSelector((state) => state.user.cart?.uuid);
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const dispatch = useShopsysDispatch();
    const [, removeItemFromCart] = useRemoveItemFromCart();
    const [, changeCartItemQuantity] = useChangeCartItemQuantity();

    const onChangeValueHandler = () => {
        if (timeoutRef.current === null) {
            timeoutRef.current = setUpdateTimeout();
        } else {
            clearTimeout(timeoutRef.current);
            timeoutRef.current = setUpdateTimeout();
        }
    };

    const onRemoveItemFromCartHanlder = () => {
        if (cartUuid === undefined) {
            return;
        }

        removeItemFromCart({ cartItemUuid: props.item.uuid, cartUuid }).then(({ data }) => {
            if (data !== undefined) {
                const newCart = mapCart(data.RemoveFromCart, currencyCode);
                dispatch(userActions.setCart(newCart));
                updateUserDataCookie({ cartUuid: newCart.uuid });
            }
        });
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
            }).then(({ data }) => {
                if (data === undefined) {
                    return;
                }

                const newCart = mapCart(data.AddToCart, currencyCode);
                dispatch(userActions.setCart(newCart));
                updateUserDataCookie({ cartUuid: newCart.uuid });
                if (data.AddToCart.addProductResult.notOnStockQuantity > 0) {
                    spinboxRef.current!.valueAsNumber = data.AddToCart.addProductResult.addedQuantity;
                    showErrorMessage(
                        t(
                            'You have the maximum available amount in your cart, you cannot add more (total {{ quantity }} {{ unitName }})',
                            {
                                quantity: data.AddToCart.addProductResult.addedQuantity,
                                unitName: props.item.product.unit.name,
                            },
                        ),
                    );
                }
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
                    {formatPrice(props.item.product.price.priceWithVat, props.item.product.price.currencyCode) +
                        '\u00A0/\u00A0' +
                        t('pc')}
                </ItemPriceStyled>
            </ItemPriceCellStyled>
            <TotalPriceCellStyled>
                <TotalPriceStyled>
                    {formatPrice(
                        props.item.product.price.priceWithVat * props.item.quantity,
                        props.item.product.price.currencyCode,
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
