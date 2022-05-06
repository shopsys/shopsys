import { getUserFriendlyErrors } from 'connectors/lib/friendlyErrorMessageParser';
import { showErrorMessage } from 'components/Helpers/Toasts';
import { useChangeTransportInCartMutationApi } from 'graphql/generated';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

export const useChangeTransportInCart = (): typeof changeTransportHandler => {
    const [, changeTransportInCart] = useChangeTransportInCartMutationApi();
    const { cartUuid } = useShopsysSelector((state) => state.user);
    const t = useTypedTranslationFunction();

    const changeTransportHandler = async (newTransportUuid: string | null, newPickupPlaceIdentifier: string | null) => {
        const changeTransportResult = await changeTransportInCart({
            input: { transportUuid: newTransportUuid, pickupPlaceIdentifier: newPickupPlaceIdentifier, cartUuid },
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

        return changeTransportResult.data?.ChangeTransportInCart;
    };

    return changeTransportHandler;
};
