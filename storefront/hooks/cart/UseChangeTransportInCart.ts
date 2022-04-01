import { showErrorMessage } from 'components/Helpers/Toasts';
import { getUserFriendlyErrors } from 'connectors/lib/friendlyErrorMessageParser';
import { useChangeTransportInCartMutationApi } from 'graphql/generated';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useShopsysSelector } from 'redux/main';
import { PickupPlaceType } from 'types/pickupPlace';
import { pushGtmTransportChangeEvent } from 'utils/Gtm/EventHandlers';
import { useGtmCartEventInfo } from 'utils/Gtm/Gtm';

export const useChangeTransportInCart = (): typeof changeTransportHandler => {
    const [, changeTransportInCart] = useChangeTransportInCartMutationApi();
    const { cartUuid } = useShopsysSelector((state) => state.user);
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const t = useTypedTranslationFunction();
    const gtmCartEventInfo = useGtmCartEventInfo();

    const changeTransportHandler = async (newTransportUuid: string | null, newPickupPlace: PickupPlaceType | null) => {
        const changeTransportResult = await changeTransportInCart({
            input: {
                transportUuid: newTransportUuid,
                pickupPlaceIdentifier: newPickupPlace?.identifier ?? null,
                cartUuid,
            },
        });

        // EXTEND TRANSPORT MODIFICATIONS HERE

        if (changeTransportResult.error !== undefined) {
            const { userError } = getUserFriendlyErrors(changeTransportResult.error, t);
            if (userError?.validation?.transport !== undefined) {
                showErrorMessage(userError.validation.transport.message);
            }
            if (userError?.validation?.pickupPlaceIdentifier !== undefined) {
                showErrorMessage(userError.validation.pickupPlaceIdentifier.message);
            }

            return null;
        }

        pushGtmTransportChangeEvent(
            gtmCartEventInfo.cart,
            changeTransportResult.data?.ChangeTransportInCart.transport ?? null,
            newPickupPlace,
            changeTransportResult.data?.ChangeTransportInCart.payment?.name,
            currencyCode,
        );

        return changeTransportResult.data?.ChangeTransportInCart;
    };

    return changeTransportHandler;
};
