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
import { formatPrice } from 'utils/formatting';
import NextLink from 'next/link';
import { ServerSidePropsType } from 'helpers/InitServerSideProps';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';
import { useShopsysSelector } from 'redux/store';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const Cart: FC<ServerSidePropsType> = (props) => {
    const t = useTypedTranslationFunction();
    const cart = useShopsysSelector((state) => state.user.cart);
    const [cartUrl] = useGetInternationalizedStaticUrls(['/cart'], props.domainConfig.url);

    return (
        <CartStyled>
            <NextLink href={cartUrl} passHref>
                <CartBlockStyled>
                    <CartPiecesStyled>
                        <CartIconStyled icon="Cart" />
                        <CartCountStyled>
                            {cart !== undefined && Array.isArray(cart.items) ? cart.items.length : 0}
                        </CartCountStyled>
                    </CartPiecesStyled>
                    <CartValueStyled>{formatPrice(0, props.domainConfig.currencyCode)}</CartValueStyled>
                </CartBlockStyled>
            </NextLink>
            <CartDetailStyled>
                <CartDetailFigureStyled>
                    <CartDetailTextStyled>{t('Your cart is currently empty.')}</CartDetailTextStyled>
                    <CartDetailImageStyled src="/images/empty-cart-small.png"></CartDetailImageStyled>
                </CartDetailFigureStyled>
            </CartDetailStyled>
            <CartButtonMobileStyled>
                <NextLink href={cartUrl} passHref>
                    <CartButtonMobileLinkStyled>
                        <CartIconMobileStyled icon="Cart" />
                        <CartCountStyled>
                            {cart !== undefined && Array.isArray(cart.items) ? cart.items.length : 0}
                        </CartCountStyled>
                    </CartButtonMobileLinkStyled>
                </NextLink>
            </CartButtonMobileStyled>
        </CartStyled>
    );
};

/* @component */
export default Cart;
