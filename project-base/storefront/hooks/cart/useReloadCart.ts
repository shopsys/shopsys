import { useChangePaymentInCart } from './useChangePaymentInCart';
import { handleCartModifications, useCurrentCart } from 'connectors/cart/Cart';
import { getCookies } from 'cookies-next';
import { CartQueryDocumentApi } from 'graphql/generated';
import { getUrlWithoutGetParameters } from 'helpers/parsing/urlParsing';
import { useIsUserLoggedIn } from 'hooks/auth/useIsUserLoggedIn';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { useEffect } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { useClient } from 'urql';

export const useReloadCart = (): void => {
    const { modifications } = useCurrentCart(false);
    const [changePaymentInCart] = useChangePaymentInCart();
    const { t } = useTranslation();
    const router = useRouter();
    const isUserLoggedIn = useIsUserLoggedIn();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const client = useClient();
    const slug = getUrlWithoutGetParameters(router.asPath);

    useEffect(() => {
        const cookies = getCookies();
        const isWithUserTokens = !!(cookies.accessToken && cookies.refreshToken);

        if ((isUserLoggedIn && !isWithUserTokens) || (!isUserLoggedIn && isWithUserTokens)) {
            router.reload();
        } else if (cartUuid || isUserLoggedIn) {
            client.query(CartQueryDocumentApi, { cartUuid }, { requestPolicy: 'network-only' }).toPromise();
        }
    }, [slug]);

    useEffect(() => {
        if (modifications) {
            handleCartModifications(modifications, t, changePaymentInCart);
        }
    }, [modifications, changePaymentInCart, t]);
};
