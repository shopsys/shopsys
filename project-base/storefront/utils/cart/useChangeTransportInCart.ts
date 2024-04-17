import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { useChangeTransportInCartMutation } from 'graphql/requests/cart/mutations/ChangeTransportInCartMutation.generated';
import { TypeListedStoreFragment } from 'graphql/requests/stores/fragments/ListedStoreFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { useGtmCartInfo } from 'gtm/utils/useGtmCartInfo';
import useTranslation from 'next-translate/useTranslation';
import { usePersistStore } from 'store/usePersistStore';
import { getUserFriendlyErrors } from 'utils/errors/friendlyErrorMessageParser';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { useLatest } from 'utils/ui/useLatest';

export type ChangeTransportHandler = (
    newTransportUuid: string | null,
    newPickupPlace: TypeListedStoreFragment | null,
) => Promise<TypeCartFragment | undefined | null>;

export const useChangeTransportInCart = (): [ChangeTransportHandler, boolean] => {
    const [{ fetching }, changeTransportInCart] = useChangeTransportInCartMutation();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const { t } = useTranslation();
    const { gtmCartInfo } = useGtmCartInfo();

    const gtmCart = useLatest(gtmCartInfo);

    const changeTransportHandler: ChangeTransportHandler = async (newTransportUuid, newPickupPlace) => {
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

        import('gtm/handlers/onGtmTransportChangeEventHandler').then(({ onGtmTransportChangeEventHandler }) => {
            onGtmTransportChangeEventHandler(
                gtmCart.current,
                changeTransportResult.data?.ChangeTransportInCart.transport ?? null,
                newPickupPlace,
                changeTransportResult.data?.ChangeTransportInCart.payment?.name,
            );
        });

        return changeTransportResult.data?.ChangeTransportInCart;
    };

    return [changeTransportHandler, fetching];
};
