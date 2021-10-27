import {
    CartSummaryLeftStyled,
    CartSummaryMiddleStyled,
    CartSummaryRightStyled,
    CartSummaryStyled,
} from './CartSummary.style';
import CartPreview from 'components/Pages/Cart/CartPreview';
import { FC } from 'react';
import FreeTransport from 'components/Blocks/FreeTransport';
import PromoCode from 'components/Blocks/PromoCode';
import Webline from 'components/Layout/Webline';

const CartSummary: FC = () => {
    return (
        <Webline>
            <CartSummaryStyled>
                <CartSummaryLeftStyled>
                    <PromoCode />
                </CartSummaryLeftStyled>
                <CartSummaryMiddleStyled>
                    <FreeTransport amountLeft={325} />
                </CartSummaryMiddleStyled>
                <CartSummaryRightStyled>
                    <CartPreview />
                </CartSummaryRightStyled>
            </CartSummaryStyled>
        </Webline>
    );
};

export default CartSummary;
