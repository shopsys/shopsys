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
import { CartListItemInfo } from './CartListItemInfo/CartListItemInfo';
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

const TEST_IDENTIFIER = 'pages-cart-list-item-';

export const CartListItem: FC<CartListItemProps> = ({ item, listIndex }) => {
    const itemCatnum = item.product.catalogNumber;

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
            changeCartItemQuantity(item.product.uuid, listIndex, spinboxRef.current!.valueAsNumber, 'cart', true);
        }, 500);
    };

    return (
        <ItemStyled data-testid={TEST_IDENTIFIER + itemCatnum}>
            <ImageCellStyled data-testid={TEST_IDENTIFIER + 'image'}>
                <NextLink href={item.product.slug} passHref>
                    <ImageWrapperStyled>
                        <Image image={item.product.image} type="thumbnailExtraSmall" alt={item.product.fullName} />
                    </ImageWrapperStyled>
                </NextLink>
            </ImageCellStyled>
            <InfoCellStyled>
                <CartListItemInfo item={item} />
            </InfoCellStyled>
            <SpinboxCellStyled data-testid={TEST_IDENTIFIER + 'spinbox'}>
                <Spinbox
                    min={1}
                    max={item.product.stockQuantity}
                    step={1}
                    defaultValue={item.quantity}
                    ref={spinboxRef}
                    onChangeValueCallback={onChangeValueHandler}
                />
            </SpinboxCellStyled>
            <ItemPriceCellStyled data-testid={TEST_IDENTIFIER + 'itemprice'}>
                <ItemPriceStyled>
                    {formatPrice(item.product.price.priceWithVat) + '\u00A0/\u00A0' + t('pc')}
                </ItemPriceStyled>
            </ItemPriceCellStyled>
            <TotalPriceCellStyled data-testid={TEST_IDENTIFIER + 'totalprice'}>
                <TotalPriceStyled>{formatPrice(item.product.price.priceWithVat * item.quantity)}</TotalPriceStyled>
            </TotalPriceCellStyled>
            <RemoveButtonCellStyled>
                <RemoveCartItemButton cartItem={item} listIndex={listIndex} />
            </RemoveButtonCellStyled>
        </ItemStyled>
    );
};
