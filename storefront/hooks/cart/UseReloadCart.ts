import { useChangePaymentInCart } from './UseChangePaymentInCart';
import { handleCartModifications, useCurrentCart } from 'connectors/cart/Cart';
import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import { useRouter } from 'next/router';
import { useEffect, useMemo } from 'react';
import { useShopsysSelector } from 'redux/main';

export const useReloadCart = (): void => {
    const { modifications, refetchCart } = useCurrentCart(false);
    const changePaymentInCart = useChangePaymentInCart();
    const t = useTypedTranslationFunction();
    const router = useRouter();
    const slug = useMemo(() => getUrlWithoutGetParameters(router.asPath), [router.asPath]);
    const { isUserLoggedIn } = useCurrentUserData();
    const { cartUuid } = useShopsysSelector((state) => state.user);

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
};
