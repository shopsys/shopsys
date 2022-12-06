import { useChangePaymentInCart } from './useChangePaymentInCart';
import { handleCartModifications, useCurrentCart } from 'connectors/cart/Cart';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import { useRouter } from 'next/router';
import { useEffect, useMemo } from 'react';
import { useShopsysSelector } from 'redux/main';

export const useReloadCart = (): void => {
    const { isCartEmpty, payment, transport, modifications, refetchCart } = useCurrentCart(false);
    const [changePaymentInCart] = useChangePaymentInCart();
    const t = useTypedTranslationFunction();
    const router = useRouter();
    const slug = useMemo(() => getUrlWithoutGetParameters(router.asPath), [router.asPath]);
    const { isUserLoggedIn } = useCurrentUserData();
    const { cartUuid } = useShopsysSelector((state) => state.user);
    const { url } = useShopsysSelector((state) => state.domain);
    const [cartUrl, transportAndPaymentUrl] = getInternationalizedStaticUrls(
        ['/cart', '/order/transport-and-payment'],
        url,
    );

    useEffect(() => {
        if (cartUuid !== null || isUserLoggedIn) {
            refetchCart();
        }
    }, [slug, refetchCart, isUserLoggedIn, cartUuid]);

    useEffect(() => {
        if (modifications !== null) {
            handleCartModifications(modifications, t, changePaymentInCart);
        }
    }, [modifications, changePaymentInCart, t]);

    useEffect(() => {
        if (isCartEmpty && ['/order/transport-and-payment', '/order/contact-information'].includes(router.route)) {
            router.replace(cartUrl);
        }
    }, [cartUrl, isCartEmpty, router]);

    useEffect(() => {
        if ((transport === null || payment === null) && router.route === '/order/contact-information') {
            router.replace(transportAndPaymentUrl);
        }
    }, [payment, router, transport, transportAndPaymentUrl]);
};
