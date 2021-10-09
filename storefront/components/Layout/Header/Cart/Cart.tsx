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
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const Cart: FC = () => {
    const t = useTypedTranslationFunction();
    const cart = useShopsysSelector((state) => state.user.cart);
    const domainConfig = useShopsysSelector((state) => state.domain);
    const [cartUrl] = useGetInternationalizedStaticUrls(['/cart'], domainConfig.url);

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
                    <CartValueStyled>
                        {formatPrice(0, domainConfig.currencyCode, { explicitZero: true })}
                    </CartValueStyled>
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
