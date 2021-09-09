import {
    CartBlockStyled,
    CartButtonMobileLinkStyled,
    CartButtonMobileStyled,
    CartCountStyled,
    CartDetailFigureStyled,
    CartDetailImageStyled,
    CartDetailStyled,
    CartDetailTextStyled,
    CartIconMobileStyled,
    CartIconStyled,
    CartPiecesStyled,
    CartStyled,
    CartValueStyled,
} from './Cart.style';
import { FC } from 'react';
import NextLink from 'next/link';
import { useTypedTranslationFunction } from 'hooks/UseTypedTranslationFunction';

const Cart: FC = () => {
    const t = useTypedTranslationFunction();

    return (
        <CartStyled>
            <CartBlockStyled>
                <CartPiecesStyled>
                    <CartIconStyled icon="Cart" />
                    <CartCountStyled>0</CartCountStyled>
                </CartPiecesStyled>
                <CartValueStyled>0 Kč</CartValueStyled>
            </CartBlockStyled>
            <CartDetailStyled>
                <CartDetailFigureStyled>
                    <CartDetailTextStyled>{t('Your cart is currently empty.')}</CartDetailTextStyled>
                    <CartDetailImageStyled src="/images/empty-cart-small.png"></CartDetailImageStyled>
                </CartDetailFigureStyled>
            </CartDetailStyled>
            <CartButtonMobileStyled>
                <NextLink href="/" passHref>
                    <CartButtonMobileLinkStyled>
                        <CartIconMobileStyled icon="Cart" />
                        <CartCountStyled>0</CartCountStyled>
                    </CartButtonMobileLinkStyled>
                </NextLink>
            </CartButtonMobileStyled>
        </CartStyled>
    );
};

/* @component */
export default Cart;
