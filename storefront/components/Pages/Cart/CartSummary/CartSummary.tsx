import { CartSummaryLeftStyled, CartSummaryRightStyled, CartSummaryStyled } from './CartSummary.style';
import CartPreview from 'components/Pages/Cart/CartPreview';
import { FC } from 'react';
import PromoCode from 'components/Blocks/PromoCode';
import Webline from 'components/Layout/Webline';

const CartSummary: FC = () => {
    return (
        <Webline>
            <CartSummaryStyled>
                <CartSummaryLeftStyled>
                    <PromoCode />
                </CartSummaryLeftStyled>
                <CartSummaryRightStyled>
                    <CartPreview />
                </CartSummaryRightStyled>
            </CartSummaryStyled>
        </Webline>
    );
};

export default CartSummary;
