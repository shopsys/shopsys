import { CartFragmentApi, ListedStoreFragmentApi, useChangeTransportInCartMutationApi } from 'graphql/generated';
import { onGtmTransportChangeEventHandler } from 'gtm/helpers/eventHandlers';
import { useGtmCartInfo } from 'gtm/helpers/gtm';
import { GtmMessageOriginType } from 'gtm/types/enums';
import { getUserFriendlyErrors } from 'helpers/errors/friendlyErrorMessageParser';
import { showErrorMessage } from 'helpers/toasts';
import { useLatest } from 'hooks/ui/useLatest';
import useTranslation from 'next-translate/useTranslation';
import { useCallback } from 'react';
import { usePersistStore } from 'store/usePersistStore';

export type ChangeTransportHandler = (
    newTransportUuid: string | null,
    newPickupPlace: ListedStoreFragmentApi | null,
) => Promise<CartFragmentApi | undefined | null>;

export const useChangeTransportInCart = (): [ChangeTransportHandler, boolean] => {
    const [{ fetching }, changeTransportInCart] = useChangeTransportInCartMutationApi();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const { t } = useTranslation();
    const { gtmCartInfo } = useGtmCartInfo();

    const gtmCart = useLatest(gtmCartInfo);

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
                    showErrorMessage(
                        userError.validation.transport.message,
                        GtmMessageOriginType.transport_and_payment_page,
                    );
                }
                if (userError?.validation?.pickupPlaceIdentifier !== undefined) {
                    showErrorMessage(
                        userError.validation.pickupPlaceIdentifier.message,
                        GtmMessageOriginType.transport_and_payment_page,
                    );
                }

                return null;
            }

            onGtmTransportChangeEventHandler(
                gtmCart.current,
                changeTransportResult.data?.ChangeTransportInCart.transport ?? null,
                newPickupPlace,
                changeTransportResult.data?.ChangeTransportInCart.payment?.name,
            );

            return changeTransportResult.data?.ChangeTransportInCart;
        },
        [cartUuid, changeTransportInCart, gtmCart, t],
    );

    return [changeTransportHandler, fetching];
};
