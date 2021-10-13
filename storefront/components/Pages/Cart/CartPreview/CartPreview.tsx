import {
    CartPreviewCellBasicPrice,
    CartPreviewCellStyled,
    CartPreviewCellTotalPrice,
    CartPreviewRowStyled,
    CartPreviewStyled,
} from './CartPreview.style';
import { FC } from 'react';
import { formatPrice } from 'utils/formatting';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const CartPreview: FC = () => {
    const t = useTypedTranslationFunction();
    const { cart } = useShopsysSelector((state) => state.user);
    const { currencyCode } = useShopsysSelector((state) => state.domain);

    if (cart === null) {
        return null;
    }

    return (
        <CartPreviewStyled>
            <tbody>
                {cart.totalDiscountPrice.priceWithVat > 0 && (
                    <CartPreviewRowStyled>
                        <CartPreviewCellStyled>{t('The amount of discounts')}</CartPreviewCellStyled>
                        <CartPreviewCellStyled textAlign="right">
                            <CartPreviewCellBasicPrice>
                                {'-' + formatPrice(cart.totalDiscountPrice.priceWithVat, currencyCode, t)}
                            </CartPreviewCellBasicPrice>
                        </CartPreviewCellStyled>
                    </CartPreviewRowStyled>
                )}
                <CartPreviewRowStyled>
                    <CartPreviewCellStyled>{t('You pay')}</CartPreviewCellStyled>
                    <CartPreviewCellStyled textAlign="right">
                        <CartPreviewCellTotalPrice>
                            {formatPrice(cart.totalPrice.priceWithVat, currencyCode, t)}
                        </CartPreviewCellTotalPrice>
                    </CartPreviewCellStyled>
                </CartPreviewRowStyled>
            </tbody>
        </CartPreviewStyled>
    );
};

export default CartPreview;
