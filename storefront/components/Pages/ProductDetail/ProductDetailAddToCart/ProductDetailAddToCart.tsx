import {
    AddToCartButtonStyled,
    AddToCartButtonsWrapperStyled,
    AddToCartFormStyled,
    AddToCartPriceStyled,
    AddtoCartSingleButtonWrapper,
    AddToCartWrapperStyled,
    SpinboxStyled,
} from './ProductDetailAddToCart.style';
import { FC } from 'react';
import { useTranslation } from 'react-i18next';

const ProductDetailAddToCart: FC = () => {
    const { t } = useTranslation();
    const props = { min: 1, max: 5, defaultValue: 1, step: 1 };
    return (
        <AddToCartWrapperStyled>
            <AddToCartPriceStyled>199,00 Kč</AddToCartPriceStyled>
            <AddToCartFormStyled>
                <AddToCartButtonsWrapperStyled>
                    <SpinboxStyled {...props} />
                    <AddtoCartSingleButtonWrapper>
                        <AddToCartButtonStyled>{t('do košíku')}</AddToCartButtonStyled>
                    </AddtoCartSingleButtonWrapper>
                </AddToCartButtonsWrapperStyled>
            </AddToCartFormStyled>
        </AddToCartWrapperStyled>
    );
};

export default ProductDetailAddToCart;
