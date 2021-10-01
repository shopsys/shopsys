import {
    CartPreviewCellBasicPrice,
    CartPreviewCellStyled,
    CartPreviewCellTotalPrice,
    CartPreviewRowStyled,
    CartPreviewStyled,
} from './CartPreview.style';
import { FC } from 'react';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const CartPreview: FC = () => {
    const t = useTypedTranslationFunction();

    return (
        <CartPreviewStyled>
            <CartPreviewRowStyled>
                <CartPreviewCellStyled>{t('The amount of discounts')}</CartPreviewCellStyled>
                <CartPreviewCellStyled textAlign="right">
                    <CartPreviewCellBasicPrice>-199,00 Kč</CartPreviewCellBasicPrice>
                </CartPreviewCellStyled>
            </CartPreviewRowStyled>
            <CartPreviewRowStyled>
                <CartPreviewCellStyled>{t('You pay')}</CartPreviewCellStyled>
                <CartPreviewCellStyled textAlign="right">
                    <CartPreviewCellTotalPrice>199,00 Kč</CartPreviewCellTotalPrice>
                </CartPreviewCellStyled>
            </CartPreviewRowStyled>
        </CartPreviewStyled>
    );
};

export default CartPreview;
