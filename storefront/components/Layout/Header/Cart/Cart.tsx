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
import { ListItem } from './ListItem/ListItem';
import { LoadingOverlay } from 'components/Basic/LoadingOverlay/LoadingOverlay';
import { Button } from 'components/Forms/Button/Button';
import { useCurrentCart } from 'connectors/cart/Cart';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { RemoveFromCartHandler, useRemoveFromCart } from 'hooks/cart/useRemoveFromCart';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useMouseHoverDebounce } from 'hooks/ui/useMouseHoverDebounce';
import NextLink from 'next/link';
import { useRouter } from 'next/router';
import { FC, useCallback, useState } from 'react';
import { useShopsysSelector } from 'redux/main';

const TEST_IDENTIFIER = 'layout-header-cart-';

export const Cart: FC = () => {
    const router = useRouter();
    const t = useTypedTranslationFunction();
    const formatPrice = useFormatPrice();
    const { cart, isCartEmpty, isInitiallyLoaded } = useCurrentCart();
    const domainConfig = useShopsysSelector((state) => state.domain);
    const [cartUrl] = getInternationalizedStaticUrls(['/cart'], domainConfig.url);
    const [onMouseEnterTrigger, setOnMouseEnterTrigger] = useState(false);
    const [onMouseLeaveTrigger, setOnMouseLeaveTrigger] = useState(false);
    const isCartHovered = useMouseHoverDebounce(onMouseEnterTrigger, onMouseLeaveTrigger);
    const { loginLoading } = useShopsysSelector((state) => state.user);
    const [removeItemFromCart, isRemovingItem] = useRemoveFromCart();

    const removeItemHandler = useCallback<RemoveFromCartHandler>(
        (cartItem, listIndex, gtmListName) => removeItemFromCart(cartItem, listIndex, gtmListName),
        [removeItemFromCart],
    );

    return (
        <CartStyled
            onMouseEnter={() => setOnMouseEnterTrigger(!onMouseEnterTrigger)}
            onMouseLeave={() => setOnMouseLeaveTrigger(!onMouseLeaveTrigger)}
        >
            {(!isInitiallyLoaded || loginLoading !== 'not-loading') && <LoadingOverlay iconSize={32} />}
            <NextLink href={cartUrl} passHref>
                <CartBlockStyled isHovered={isCartHovered} data-testid={TEST_IDENTIFIER + 'block'}>
                    <CartPiecesStyled>
                        <CartIconStyled iconType="icon" icon="Cart" />
                        <CartCountStyled data-testid={TEST_IDENTIFIER + 'itemcount'}>
                            {cart?.items.length ?? 0}
                        </CartCountStyled>
                    </CartPiecesStyled>
                    <CartValueStyled data-testid={TEST_IDENTIFIER + 'totalprice'}>
                        {formatPrice(cart?.totalItemsPrice.priceWithVat ?? 0, {
                            explicitZero: true,
                        })}
                    </CartValueStyled>
                </CartBlockStyled>
            </NextLink>
            <CartDetailStyled
                containsProducts={!isCartEmpty}
                isHovered={isCartHovered}
                data-testid={TEST_IDENTIFIER + 'detail'}
            >
                {!isCartEmpty ? (
                    <>
                        <CartDetailList>
                            {isRemovingItem && <LoadingOverlay iconSize={64} />}
                            {cart?.items.map((cartItem, index) => (
                                <ListItem
                                    key={cartItem.uuid}
                                    cartItem={cartItem}
                                    onItemRemove={() => removeItemHandler(cartItem, index, 'cart')}
                                />
                            ))}
                        </CartDetailList>
                        <CartDetailButtonWrapperStyled>
                            <Button
                                type="button"
                                size="small"
                                onClick={() => router.push(cartUrl)}
                                testIdentifier={TEST_IDENTIFIER + 'button'}
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
