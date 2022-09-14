import {
    CartSummaryLeftStyled,
    CartSummaryMiddleStyled,
    CartSummaryRightStyled,
    CartSummaryStyled,
} from './CartSummary.style';
import { FreeTransport } from 'components/Blocks/FreeTransport/FreeTransport';
import { PromoCode } from 'components/Blocks/PromoCode/PromoCode';
import { Webline } from 'components/Layout/Webline/Webline';
import { CartPreview } from 'components/Pages/Cart/CartPreview/CartPreview';
import { FC } from 'react';

export const CartSummary: FC = () => (
    <Webline>
        <CartSummaryStyled>
            <CartSummaryLeftStyled>
                <PromoCode />
            </CartSummaryLeftStyled>
            <CartSummaryMiddleStyled>
                <FreeTransport />
            </CartSummaryMiddleStyled>
            <CartSummaryRightStyled>
                <CartPreview />
            </CartSummaryRightStyled>
        </CartSummaryStyled>
    </Webline>
);
