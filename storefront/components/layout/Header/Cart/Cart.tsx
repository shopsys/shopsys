import {
    CartBlockStyled,
    CartButtonMobileLinkStyled,
    CartButtonMobileStyled,
    CartCountStyled,
    CartDetailFigureStyled,
    CartDetailImageStyled,
    CartDetailStyled,
    CartDetailTextStyled,
    CartPiecesStyled,
    CartStyled,
    CartValueStyled,
} from './Cart.style';
import Link from 'next/link';
import { ReactElement } from 'react';
import ShopsysIcon from '../../../basic/ShopsysIcon';
import { useTranslation } from 'react-i18next';

const Cart = (): ReactElement => {
    const { t } = useTranslation();

    return (
        <CartStyled>
            <CartBlockStyled>
                <CartPiecesStyled>
                    <ShopsysIcon icon="cart" iconHeight={18} />
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
                        <ShopsysIcon icon="cart" iconHeight={18} />
                        <CartCountStyled>0</CartCountStyled>
                    </CartButtonMobileLinkStyled>
                </Link>
            </CartButtonMobileStyled>
        </CartStyled>
    );
};

/* @component */
export default Cart;
