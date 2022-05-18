import {
    CartPreviewCellBasicPrice,
    CartPreviewCellStyled,
    CartPreviewCellTotalPrice,
    CartPreviewRowStyled,
    CartPreviewStyled,
} from './CartPreview.style';
import { useCurrentCart } from 'connectors/cart/Cart';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';
import { useShopsysSelector } from 'redux/main';
import { formatPrice } from 'utils/formatting';

const CartPreview: FC = () => {
    const testIdentifier = 'pages-cart-cartpreview';

    const t = useTypedTranslationFunction();
    const { cart, isCartEmpty } = useCurrentCart();
    const { currencyCode } = useShopsysSelector((state) => state.domain);

    if (cart === null || isCartEmpty) {
        return null;
    }

    return (
        <CartPreviewStyled data-testid={testIdentifier}>
            <tbody>
                {cart.totalDiscountPrice.priceWithVat > 0 && (
                    <CartPreviewRowStyled data-testid={testIdentifier + '-discount'}>
                        <CartPreviewCellStyled>{t('The amount of discounts')}</CartPreviewCellStyled>
                        <CartPreviewCellStyled textAlign="right">
                            <CartPreviewCellBasicPrice>
                                {'-' + formatPrice(cart.totalDiscountPrice.priceWithVat, currencyCode, t)}
                            </CartPreviewCellBasicPrice>
                        </CartPreviewCellStyled>
                    </CartPreviewRowStyled>
                )}
                <CartPreviewRowStyled data-testid={testIdentifier + '-total'}>
                    <CartPreviewCellStyled>{t('You pay')}</CartPreviewCellStyled>
                    <CartPreviewCellStyled textAlign="right">
                        <CartPreviewCellTotalPrice>
                            {formatPrice(cart.totalItemsPrice.priceWithVat, currencyCode, t)}
                        </CartPreviewCellTotalPrice>
                    </CartPreviewCellStyled>
                </CartPreviewRowStyled>
            </tbody>
        </CartPreviewStyled>
    );
};

export default CartPreview;
