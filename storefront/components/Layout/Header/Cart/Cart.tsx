import {
    CartBlockStyled,
    CartButtonMobileLinkStyled,
    CartButtonMobileStyled,
    CartCountStyled,
    CartDetailButtonWrapperStyled,
    CartDetailFigureStyled,
    CartDetailImageStyled,
    CartDetailList,
    CartDetailStyled,
    CartDetailTextStyled,
    CartIconMobileStyled,
    CartIconStyled,
    CartPiecesStyled,
    CartStyled,
    CartValueStyled,
} from './Cart.style';
import { FC, useState } from 'react';
import Button from 'components/Forms/Button';
import { formatPrice } from 'utils/formatting';
import ListItem from './ListItem';
import NextLink from 'next/link';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';
import { useRouter } from 'next/router';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const Cart: FC = () => {
    const router = useRouter();
    const t = useTypedTranslationFunction();
    const { cart, isCartEmpty } = useShopsysSelector((state) => state.cart);
    const domainConfig = useShopsysSelector((state) => state.domain);
    const [cartUrl] = useGetInternationalizedStaticUrls(['/cart'], domainConfig.url);
    const [isCartHovered, setIsCartHovered] = useState(false);

    return (
        <CartStyled onMouseEnter={() => setIsCartHovered(true)} onMouseLeave={() => setIsCartHovered(false)}>
            <NextLink href={cartUrl} passHref>
                <CartBlockStyled isHovered={isCartHovered}>
                    <CartPiecesStyled>
                        <CartIconStyled iconType="icon" icon="Cart" />
                        <CartCountStyled>{cart !== null && !isCartEmpty ? cart.items.length : 0}</CartCountStyled>
                    </CartPiecesStyled>
                    <CartValueStyled>
                        {formatPrice(
                            cart?.totalItemsPrice.priceWithVat === undefined ? 0 : cart.totalItemsPrice.priceWithVat,
                            domainConfig.currencyCode,
                            t,
                            {
                                explicitZero: true,
                            },
                        )}
                    </CartValueStyled>
                </CartBlockStyled>
            </NextLink>
            <CartDetailStyled containsProducts={!isCartEmpty} isHovered={isCartHovered}>
                {cart !== null && !isCartEmpty ? (
                    <>
                        <CartDetailList>
                            {cart.items.map((cartItem) => (
                                <ListItem key={cartItem.uuid} cartItem={cartItem} />
                            ))}
                        </CartDetailList>
                        <CartDetailButtonWrapperStyled>
                            <Button type="button" size="small" onClick={() => router.push(cartUrl)}>
                                {t('Go to cart')}
                            </Button>
                        </CartDetailButtonWrapperStyled>
                    </>
                ) : (
                    <CartDetailFigureStyled>
                        <CartDetailTextStyled>{t('Your cart is currently empty.')}</CartDetailTextStyled>
                        <CartDetailImageStyled src="/images/empty-cart-small.png"></CartDetailImageStyled>
                    </CartDetailFigureStyled>
                )}
            </CartDetailStyled>
            <CartButtonMobileStyled>
                <NextLink href={cartUrl} passHref>
                    <CartButtonMobileLinkStyled>
                        <CartIconMobileStyled iconType="icon" icon="Cart" />
                        <CartCountStyled>{cart !== null && !isCartEmpty ? cart.items.length : 0}</CartCountStyled>
                    </CartButtonMobileLinkStyled>
                </NextLink>
            </CartButtonMobileStyled>
        </CartStyled>
    );
};

/* @component */
export default Cart;
