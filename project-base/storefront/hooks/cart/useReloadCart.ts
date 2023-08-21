import { useChangePaymentInCart } from './useChangePaymentInCart';
import { handleCartModifications, useCurrentCart } from 'connectors/cart/Cart';
import { getUrlWithoutGetParameters } from 'helpers/parsing/urlParsing';
import useTranslation from 'next-translate/useTranslation';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { useRouter } from 'next/router';
import { useEffect } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { useClient } from 'urql';
import { CartQueryDocumentApi } from 'graphql/generated';

export const useReloadCart = (): void => {
    const { modifications } = useCurrentCart(false);
    const [changePaymentInCart] = useChangePaymentInCart();
    const { t } = useTranslation();
    const router = useRouter();
    const slug = getUrlWithoutGetParameters(router.asPath);
    const isUserLoggedIn = !!useCurrentCustomerData();
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