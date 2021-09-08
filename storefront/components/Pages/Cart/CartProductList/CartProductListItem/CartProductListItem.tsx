import { AppDispatch, useShopsysDispatch } from 'redux/store';
import { CartItemType, CartType } from 'connectors/cart/types';
import {
    CartProductImageCellStyled,
    CartProductInfoCellStyled,
    CartProductItemPriceCellStyled,
    CartProductItemPriceStyled,
    CartProductListItemStyled,
    CartProductRemoveButtonCellStyled,
    CartProductRemoveButtonStyled,
    CartProductSpinboxCellStyled,
    CartProductTotalPriceCellStyled,
    CartProductTotalPriceStyled,
} from './CartProductListItem.style';
import CartProductListItemInfo from './CartProductListItemInfo';
import { FC } from 'react';
import { formatPrice } from 'utils/formatting';
// import { getUserFriendlyErrors } from 'connectors/lib/friendlyErrorMessageParser';
import Link from 'next/link';
import { OperationResult } from '@urql/core';
import ShopsysIcon from 'components/basic/ShopsysIcon';
import ShopsysImage from 'components/basic/ShopsysImage';
import ShopsysSpinbox from 'components/forms/ShopsysSpinbox';
import { userActions } from 'redux/store/UserStore';
import { useRemoveItemFromCart } from 'connectors/cart/Cart';
import { useTranslation } from 'react-i18next';

type CartProductListItemProps = {
    item: CartItemType;
    cartUuid: string;
};

const CartProductListItem: FC<CartProductListItemProps> = (props) => {
    const { t } = useTranslation();
    const [, removeItemFromCart] = useRemoveItemFromCart();
    const dispatch = useShopsysDispatch();

    const onRemoveItemFromCartHanlder = () => {
        removeItemFromCart({ cartItemUuid: props.item.uuid, cartUuid: props.cartUuid }).then((result) => {
            handleCartUpdate(result, dispatch);
        });
    };

    return (
        <CartProductListItemStyled>
            <CartProductImageCellStyled>
                <Link href={props.item.product.slug} passHref>
                    <a>
                        <ShopsysImage image={props.item.product.image} alt={props.item.product.name} />
                    </a>
                </Link>
            </CartProductImageCellStyled>
            <CartProductInfoCellStyled>
                <CartProductListItemInfo item={props.item} />
            </CartProductInfoCellStyled>
            <CartProductSpinboxCellStyled>
                <ShopsysSpinbox />
            </CartProductSpinboxCellStyled>
            <CartProductItemPriceCellStyled>
                <CartProductItemPriceStyled isInSale={props.item.product.isInSale}>
                    {formatPrice(props.item.product.price.priceWithVat, props.item.product.price.currencyCode) +
                        '\u00A0/\u00A0' +
                        t('pc')}
                </CartProductItemPriceStyled>
            </CartProductItemPriceCellStyled>
            <CartProductTotalPriceCellStyled>
                <CartProductTotalPriceStyled isInSale={props.item.product.isInSale}>
                    {formatPrice(
                        props.item.product.price.priceWithVat * props.item.quantity,
                        props.item.product.price.currencyCode,
                    )}
                </CartProductTotalPriceStyled>
            </CartProductTotalPriceCellStyled>
            <CartProductRemoveButtonCellStyled>
                <CartProductRemoveButtonStyled onClick={onRemoveItemFromCartHanlder}>
                    <ShopsysIcon icon="remove-bold" iconHeight={7} />
                </CartProductRemoveButtonStyled>
            </CartProductRemoveButtonCellStyled>
        </CartProductListItemStyled>
    );
};

const handleCartUpdate = (updateResult: OperationResult<CartType>, dispatch: AppDispatch) => {
    if (updateResult.error !== undefined) {
        // handle errors globally?
    } else {
        dispatch(userActions.setCart(updateResult.data));
    }
};

export default CartProductListItem;
