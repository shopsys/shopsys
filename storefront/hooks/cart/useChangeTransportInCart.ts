import { showErrorMessage } from 'components/Helpers/Toasts';
import { getUserFriendlyErrors } from 'connectors/lib/friendlyErrorMessageParser';
import { CartFragmentApi, useChangeTransportInCartMutationApi } from 'graphql/generated';
import { onTransportChangeGtmEventHandler } from 'helpers/gtm/eventHandlers';
import { useGtmCartEventInfo } from 'helpers/gtm/gtm';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useLatest } from 'hooks/ui/useLatest';
import { useCallback } from 'react';
import { useShopsysSelector } from 'redux/main';
import { PickupPlaceType } from 'types/pickupPlace';

export type ChangeTransportHandler = (
    newTransportUuid: string | null,
    newPickupPlace: PickupPlaceType | null,
) => Promise<CartFragmentApi | undefined | null>;

export const useChangeTransportInCart = (): [ChangeTransportHandler, boolean] => {
    const [{ fetching }, changeTransportInCart] = useChangeTransportInCartMutationApi();
    const { cartUuid } = useShopsysSelector((state) => state.user);
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const t = useTypedTranslationFunction();
    const gtmCartEventInfo = useGtmCartEventInfo();

    const gtmCart = useLatest(gtmCartEventInfo.cart);

    const changeTransportHandler = useCallback<ChangeTransportHandler>(
        async (newTransportUuid, newPickupPlace) => {
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

    return [changeTransportHandler, fetching];
};
