import {
    ImageCellStyled,
    ImageWrapperStyled,
    InfoCellStyled,
    ItemPriceCellStyled,
    ItemPriceStyled,
    ItemStyled,
    RemoveButtonCellStyled,
    SpinboxCellStyled,
    TotalPriceCellStyled,
    TotalPriceStyled,
} from './CartListItem.style';
import { CartListItemInfo } from './CartListItemInfo/CartListItem';
import { Image } from 'components/Basic/Image/Image';
import { Spinbox } from 'components/Forms/Spinbox/Spinbox';
import { RemoveCartItemButton } from 'components/Pages/Cart/RemoveCartItemButton/RemoveCartItemButton';
import { useAddToCart } from 'hooks/cart/UseAddToCart';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import NextLink from 'next/link';
import { FC, useRef } from 'react';
import { CartItemType } from 'types/cart';

type CartListItemProps = {
    item: CartItemType;
    listIndex: number;
};

export const CartListItem: FC<CartListItemProps> = (props) => {
    const testIdentifier = 'pages-cart-list-item-';
    const itemCatnum = props.item.product.catalogNumber;

    const timeoutRef = useRef<NodeJS.Timeout | null>(null);
    const spinboxRef = useRef<HTMLInputElement>(null);
    const t = useTypedTranslationFunction();
    const formatPrice = useFormatPrice();
    const changeCartItemQuantity = useAddToCart('cart');

    const onChangeValueHandler = () => {
        if (timeoutRef.current === null) {
            timeoutRef.current = setUpdateTimeout();
        } else {
            clearTimeout(timeoutRef.current);
            timeoutRef.current = setUpdateTimeout();
        }
    };

    const setUpdateTimeout = () => {
        return setTimeout(() => {
            changeCartItemQuantity(
                props.item.product.uuid,
                props.listIndex,
                spinboxRef.current!.valueAsNumber,
                'cart',
                true,
            );
        }, 500);
    };

    return (
        <ItemStyled data-testid={testIdentifier + itemCatnum}>
            <ImageCellStyled data-testid={testIdentifier + 'image'}>
                <NextLink href={props.item.product.slug} passHref>
                    <ImageWrapperStyled>
                        <Image
                            image={props.item.product.image}
                            type="thumbnailExtraSmall"
                            alt={props.item.product.fullName}
                        />
                    </ImageWrapperStyled>
                </NextLink>
            </ImageCellStyled>
            <InfoCellStyled>
                <CartListItemInfo item={props.item} />
            </InfoCellStyled>
            <SpinboxCellStyled data-testid={testIdentifier + 'spinbox'}>
                <Spinbox
                    min={1}
                    max={props.item.product.stockQuantity}
                    step={1}
                    defaultValue={props.item.quantity}
                    ref={spinboxRef}
                    onChangeValueCallback={onChangeValueHandler}
                />
            </SpinboxCellStyled>
            <ItemPriceCellStyled data-testid={testIdentifier + 'itemprice'}>
                <ItemPriceStyled>
                    {formatPrice(props.item.product.price.priceWithVat) + '\u00A0/\u00A0' + t('pc')}
                </ItemPriceStyled>
            </ItemPriceCellStyled>
            <TotalPriceCellStyled data-testid={testIdentifier + 'totalprice'}>
                <TotalPriceStyled>
                    {formatPrice(props.item.product.price.priceWithVat * props.item.quantity)}
                </TotalPriceStyled>
            </TotalPriceCellStyled>
            <RemoveButtonCellStyled>
                <RemoveCartItemButton cartItem={props.item} listIndex={props.listIndex} />
            </RemoveButtonCellStyled>
        </ItemStyled>
    );
};
