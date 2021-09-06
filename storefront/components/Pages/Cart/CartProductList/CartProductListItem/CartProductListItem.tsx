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
import { CartItemType } from 'connectors/cart/types';
import CartProductListItemInfo from './CartProductListItemInfo';
import { FC } from 'react';
import { formatPrice } from 'utils/formatting';
import Link from 'next/link';
import ShopsysIcon from 'components/basic/ShopsysIcon';
import ShopsysImage from 'components/basic/ShopsysImage';
import ShopsysSpinbox from 'components/forms/ShopsysSpinbox';
import { useTranslation } from 'react-i18next';

type CartProductListItemProps = {
    item: CartItemType;
};

const CartProductListItem: FC<CartProductListItemProps> = (props) => {
    const { t } = useTranslation();

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
                <CartProductRemoveButtonStyled>
                    <ShopsysIcon icon="remove-bold" iconHeight={7} />
                </CartProductRemoveButtonStyled>
            </CartProductRemoveButtonCellStyled>
        </CartProductListItemStyled>
    );
};

export default CartProductListItem;
