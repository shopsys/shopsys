import { handleCartModifications } from 'connectors/cart/Cart';
import {
    CartQueryDocument,
    TypeCartQuery,
    TypeCartQueryVariables,
} from 'graphql/requests/cart/queries/CartQuery.generated';
import { useCallback, useEffect, useEffectEvent } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { useClient } from 'urql';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useRefetchOnTabFocus } from 'utils/useRefetchOnTabFocus';
import { useChangePaymentInCart } from './useChangePaymentInCart';
import { useCurrentCart } from './useCurrentCart';

export const useReloadCart = (): void => {
    const { modifications } = useCurrentCart();
    const { changePaymentInCart } = useChangePaymentInCart();
    const { t } = useTranslation();
    const client = useClient();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const isUserLoggedIn = useIsUserLoggedIn();

    const refetchCart = useCallback(() => {
        if (!cartUuid && !isUserLoggedIn) {
            return;
        }

        client
            .query<TypeCartQuery, TypeCartQueryVariables>(
                CartQueryDocument,
                { cartUuid },
                { requestPolicy: 'network-only' },
            )
            .toPromise();
    }, [client, cartUuid, isUserLoggedIn]);

    useRefetchOnTabFocus(refetchCart);

    const onHandleModifications = useEffectEvent(() => {
        if (modifications) {
            handleCartModifications(modifications, t, changePaymentInCart);
        }
    });

    useEffect(() => {
        if (modifications) {
            onHandleModifications();
        }
    }, [modifications]);
};
