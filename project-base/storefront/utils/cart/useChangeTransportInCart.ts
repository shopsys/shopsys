import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { useChangeTransportInCartMutation } from 'graphql/requests/cart/mutations/ChangeTransportInCartMutation.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { useGtmCartInfo } from 'gtm/utils/useGtmCartInfo';
import useTranslation from 'next-translate/useTranslation';
import { usePersistStore } from 'store/usePersistStore';
import { getUserFriendlyErrors } from 'utils/errors/friendlyErrorMessageParser';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { useLatest } from 'utils/ui/useLatest';

export type ChangeTransportInCart = (
    newTransportUuid: string | null,
    newPickupPlace: StoreOrPacketeryPoint | null,
) => Promise<TypeCartFragment | undefined | null>;

export const useChangeTransportInCart = () => {
    const [{ fetching: isChangingTransportInCart }, changeTransportInCartMutation] = useChangeTransportInCartMutation();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const { t } = useTranslation();
    const { gtmCartInfo } = useGtmCartInfo();
    const currentCustomerData = useCurrentCustomerData();

    const gtmCart = useLatest(gtmCartInfo);

    const changeTransportInCart: ChangeTransportInCart = async (newTransportUuid, newPickupPlace) => {
        const changeTransportResult = await changeTransportInCartMutation(
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
                !!currentCustomerData?.arePricesHidden,
            );
        });

        return changeTransportResult.data?.ChangeTransportInCart;
    };

    return { changeTransportInCart, isChangingTransportInCart };
};
