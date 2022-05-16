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
import ListItem from './ListItem';
import Button from 'components/Forms/Button';
import { useCurrentCart } from 'connectors/cart/Cart';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useMouseHoverDebounce } from 'hooks/ui/useMouseHoverDebounce';
import NextLink from 'next/link';
import { useRouter } from 'next/router';
import { FC, useState } from 'react';
import { useShopsysSelector } from 'redux/main';
import { formatPrice } from 'utils/formatting';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';

const Cart: FC = () => {
    const testIdentifier = 'layout-header-cart-';

    const router = useRouter();
    const t = useTypedTranslationFunction();
    const { cart, isCartEmpty } = useCurrentCart();
    const domainConfig = useShopsysSelector((state) => state.domain);
    const [cartUrl] = getInternationalizedStaticUrls(['/cart'], domainConfig.url);
    const [onMouseEnterTrigger, setOnMouseEnterTrigger] = useState(false);
    const [onMouseLeaveTrigger, setOnMouseLeaveTrigger] = useState(false);
    const isCartHovered = useMouseHoverDebounce(onMouseEnterTrigger, onMouseLeaveTrigger);

    return (
        <CartStyled
            onMouseEnter={() => setOnMouseEnterTrigger(!onMouseEnterTrigger)}
            onMouseLeave={() => setOnMouseLeaveTrigger(!onMouseLeaveTrigger)}
        >
            <NextLink href={cartUrl} passHref>
                <CartBlockStyled isHovered={isCartHovered} data-testid={testIdentifier + 'block'}>
                    <CartPiecesStyled>
                        <CartIconStyled iconType="icon" icon="Cart" />
                        <CartCountStyled data-testid={testIdentifier + 'itemcount'}>
                            {cart?.items.length ?? 0}
                        </CartCountStyled>
                    </CartPiecesStyled>
                    <CartValueStyled data-testid={testIdentifier + 'totalprice'}>
                        {formatPrice(cart?.totalItemsPrice.priceWithVat ?? 0, domainConfig.currencyCode, t, {
                            explicitZero: true,
                        })}
                    </CartValueStyled>
                </CartBlockStyled>
            </NextLink>
            <CartDetailStyled
                containsProducts={!isCartEmpty}
                isHovered={isCartHovered}
                data-testid={testIdentifier + 'detail'}
            >
                {!isCartEmpty ? (
                    <>
                        <CartDetailList>
                            {cart?.items.map((cartItem) => (
                                <ListItem key={cartItem.uuid} cartItem={cartItem} />
                            ))}
                        </CartDetailList>
                        <CartDetailButtonWrapperStyled>
                            <Button
                                type="button"
                                size="small"
                                onClick={() => router.push(cartUrl)}
                                data-testid={testIdentifier + 'button'}
                            >
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
                        <CartCountStyled>{cart?.items.length ?? 0}</CartCountStyled>
                    </CartButtonMobileLinkStyled>
                </NextLink>
            </CartButtonMobileStyled>
        </CartStyled>
    );
};

/* @component */
export default Cart;
