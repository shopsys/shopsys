import {
    CartBlockStyled,
    CartButtonMobileLinkStyled,
    CartButtonMobileStyled,
    CartCountStyled,
    CartDetailFigureStyled,
    CartDetailImageStyled,
    CartDetailStyled,
    CartDetailTextStyled,
    CartIconStyled,
    CartPiecesStyled,
    CartStyled,
    CartValueStyled,
} from './Cart.style';
import Link from 'next/link';
import { ReactElement } from 'react';
import { useTranslation } from 'react-i18next';

const Cart = (): ReactElement => {
    const { t } = useTranslation();

    return (
        <CartStyled>
            <CartBlockStyled>
                <CartPiecesStyled>
                    <CartIconStyled src="/svg/cart.svg" alt="" />
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
                <Link href="/" passHref>
                    <CartButtonMobileLinkStyled>
                        <CartIconStyled src="/svg/cart.svg" alt="" />
                        <CartCountStyled>0</CartCountStyled>
                    </CartButtonMobileLinkStyled>
                </Link>
            </CartButtonMobileStyled>
        </CartStyled>
    );
};

/* @component */
export default Cart;
