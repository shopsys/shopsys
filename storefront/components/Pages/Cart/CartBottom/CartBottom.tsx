import { CartBottomLeftStyled, CartBottomRightStyled, CartBottomStyled } from './CartBottom.style';
import CartPreview from 'components/Pages/Cart/CartPreview';
import { FC } from 'react';
import Webline from 'components/Layout/Webline';

const CartBottom: FC = () => {
    return (
        <Webline>
            <CartBottomStyled>
                <CartBottomLeftStyled>Promo code</CartBottomLeftStyled>
                <CartBottomRightStyled>
                    <CartPreview />
                </CartBottomRightStyled>
            </CartBottomStyled>
        </Webline>
    );
};

export default CartBottom;
