import { useChangePaymentInCart } from './useChangePaymentInCart';
import { handleCartModifications, useCurrentCart } from 'connectors/cart/Cart';
import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import { useRouter } from 'next/router';
import { useEffect } from 'react';
import { usePersistStore } from 'store/zustand/usePersistStore';
import { useClient } from 'urql';
import { CartQueryDocumentApi } from 'graphql/generated';

export const useReloadCart = (): void => {
    const { modifications } = useCurrentCart(false);
    const [changePaymentInCart] = useChangePaymentInCart();
    const t = useTypedTranslationFunction();
    const router = useRouter();
    const slug = getUrlWithoutGetParameters(router.asPath);
    const { isUserLoggedIn } = useCurrentUserData();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const client = useClient();

    useEffect(() => {
        if (cartUuid || isUserLoggedIn) {
            client.query(CartQueryDocumentApi, { cartUuid }, { requestPolicy: 'network-only' }).toPromise();
        }
    }, [slug]);

    useEffect(() => {
        if (modifications) {
            handleCartModifications(modifications, t, changePaymentInCart);
        }
    }, [modifications, changePaymentInCart, t]);
};
