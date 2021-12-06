import { CartQueryDocumentApi } from 'graphql/generated';
import { useClient } from 'urql';
import { useEffect } from 'react';
import { useRouter } from 'next/router';
import { useShopsysSelector } from 'redux/main';

export const useRefreshCartOnNavigation = (): void => {
    const router = useRouter();
    const { cartUuid, isCartEmpty, transport, payment, promoCode } = useShopsysSelector((state) => state.cartInput);
    const client = useClient();

    useEffect(() => {
        if (!isCartEmpty) {
            client.query(CartQueryDocumentApi, {
                variables: { cartUuid, transport, payment, promoCode },
            });
        }
    }, [router.asPath, isCartEmpty]);
};
