import { CartApiType, CartInput } from 'connectors/cart/types';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { PaymentApiType } from 'connectors/payments/types';
import { TransportApiType } from 'connectors/transports/types';
import { updateStateAndCookie } from 'utils/Cart/UpdateStateAndCookie';
import { useEffect } from 'react';
import { UseQueryState } from 'urql';

export const useHandleCartUpdate = (
    result: UseQueryState<{ cart: CartApiType & { transport: TransportApiType; payment: PaymentApiType } }, CartInput>,
    personalPickupStoreUuid: string | null,
    promoCode: string | null,
): void => {
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const dispatch = useShopsysDispatch();
    useEffect(() => {
        if (result.data === undefined) {
            return;
        }

        // TODO handle modifications

        updateStateAndCookie(result.data.cart, dispatch, personalPickupStoreUuid, promoCode, currencyCode);
    }, [result.data]);
};
