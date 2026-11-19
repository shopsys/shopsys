import {
    CartQueryDocument,
    TypeCartQuery,
    TypeCartQueryVariables,
} from 'graphql/requests/cart/queries/CartQuery.generated';
import { useCallback } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { useClient } from 'urql';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { useRefetchOnTabFocus } from 'utils/useRefetchOnTabFocus';

export const useReloadCart = (): void => {
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
};
