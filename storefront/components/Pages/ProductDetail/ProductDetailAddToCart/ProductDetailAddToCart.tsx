import {
    AddToCartButtonStyled,
    AddToCartButtonsWrapperStyled,
    AddToCartButtonWrapperStyled,
    AddToCartFormStyled,
    AddToCartPriceStyled,
    AddToCartWrapperStyled,
} from './ProductDetailAddToCart.style';
import { FC } from 'react';
import { formatPrice } from 'utils/formatting';
import Spinbox from 'components/Forms/Spinbox';
import { useHandleChangeCartItemQuantity } from 'hooks/cart/UseHandleChangeCartItemQuantity';
import { userActions } from 'redux/store/UserStore';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const ProductDetailAddToCart: FC = () => {
    const t = useTypedTranslationFunction();
    const props = { min: 1, max: 5, defaultValue: 1, step: 1 };

    return (
        <AddToCartWrapperStyled>
            {/* TODO PRG: join live data */}
            <AddToCartPriceStyled>{formatPrice(199, 'CZK')}</AddToCartPriceStyled>
            <AddToCartFormStyled>
                <AddToCartButtonsWrapperStyled>
                    <Spinbox {...props} />
                    <AddToCartButtonWrapperStyled>
                        <AddToCartButtonStyled>{t('Add to cart')}</AddToCartButtonStyled>
                    </AddToCartButtonWrapperStyled>
                </AddToCartButtonsWrapperStyled>
            </AddToCartFormStyled>
        </AddToCartWrapperStyled>
    );
};

export default ProductDetailAddToCart;
