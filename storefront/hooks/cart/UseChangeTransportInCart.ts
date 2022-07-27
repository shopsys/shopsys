import { showErrorMessage } from 'components/Helpers/Toasts';
import { getUserFriendlyErrors } from 'connectors/lib/friendlyErrorMessageParser';
import { useChangeTransportInCartMutationApi } from 'graphql/generated';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useLatest } from 'hooks/ui/useLatest';
import { useCallback } from 'react';
import { useShopsysSelector } from 'redux/main';
import { PickupPlaceType } from 'types/pickupPlace';
import { onTransportChangeGtmEventHandler } from 'utils/Gtm/EventHandlers';
import { useGtmCartEventInfo } from 'utils/Gtm/Gtm';

export const useChangeTransportInCart = (): typeof changeTransportHandler => {
    const [, changeTransportInCart] = useChangeTransportInCartMutationApi();
    const { cartUuid } = useShopsysSelector((state) => state.user);
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const t = useTypedTranslationFunction();
    const gtmCartEventInfo = useGtmCartEventInfo();

    const gtmCart = useLatest(gtmCartEventInfo.cart);

    const changeTransportHandler = useCallback(
        async (newTransportUuid: string | null, newPickupPlace: PickupPlaceType | null) => {
            const changeTransportResult = await changeTransportInCart(
                {
                    input: {
                        transportUuid: newTransportUuid,
                        pickupPlaceIdentifier: newPickupPlace?.identifier ?? null,
                        cartUuid,
                    },
                },
                { additionalTypenames: ['dedup'] },
            );

            // EXTEND TRANSPORT MODIFICATIONS HERE

            if (changeTransportResult.error !== undefined) {
                const { userError } = getUserFriendlyErrors(changeTransportResult.error, t);
                if (userError?.validation?.transport !== undefined) {
                    showErrorMessage(userError.validation.transport.message, 'transport pay');
                }
                if (userError?.validation?.pickupPlaceIdentifier !== undefined) {
                    showErrorMessage(userError.validation.pickupPlaceIdentifier.message, 'transport pay');
                }

                return null;
            }

            onTransportChangeGtmEventHandler(
                gtmCart.current,
                changeTransportResult.data?.ChangeTransportInCart.transport ?? null,
                newPickupPlace,
                changeTransportResult.data?.ChangeTransportInCart.payment?.name,
                currencyCode,
            );

            return changeTransportResult.data?.ChangeTransportInCart;
        },
        [cartUuid, changeTransportInCart, currencyCode, gtmCart, t],
    );

    return changeTransportHandler;
};
