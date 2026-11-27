import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { useChangeTransportInCartMutation } from 'graphql/requests/cart/mutations/ChangeTransportInCartMutation.generated';
import { TypeTransportWithAvailablePaymentsFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsFragment.generated';
import { useGtmCartInfo } from 'gtm/utils/useGtmCartInfo';
import { usePersistStore } from 'store/usePersistStore';
import { useDisplayTimezone } from 'utils/formatting/useDisplayTimezone';
import { getPickupPlaceClosedDates } from 'utils/packetery/getPickupPlaceClosedDates';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { isPacketeryTransport } from 'utils/transport';
import { useLatest } from 'utils/ui/useLatest';

export type ChangeTransportInCart = (
    newTransport: Pick<TypeTransportWithAvailablePaymentsFragment, 'uuid' | 'transportTypeCode'> | null,
    newPickupPlace: StoreOrPacketeryPoint | null,
    options?: { suppressValidationErrors?: boolean },
) => Promise<TypeCartFragment | undefined | null>;

export const useChangeTransportInCart = () => {
    const [{ fetching: isChangingTransportInCart }, changeTransportInCartMutation] = useChangeTransportInCartMutation();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const { gtmCartInfo } = useGtmCartInfo();
    const { canSeePrices } = useAuthorization();
    const displayTimezone = useDisplayTimezone();

    const gtmCart = useLatest(gtmCartInfo);

    const changeTransportInCart: ChangeTransportInCart = async (newTransport, newPickupPlace, options) => {
        const changeTransportResult = await changeTransportInCartMutation(
            {
                input: {
                    transportUuid: newTransport?.uuid ?? null,
                    pickupPlaceIdentifier: newPickupPlace?.identifier ?? null,
                    // only the opening hours of a carrier's pickup place are unknown to the API, the stores' ones are not
                    pickupPlaceClosedDates:
                        newPickupPlace && isPacketeryTransport(newTransport?.transportTypeCode)
                            ? getPickupPlaceClosedDates(newPickupPlace, new Date(), displayTimezone)
                            : [],
                    cartUuid,
                },
            },
            { additionalTypenames: ['dedup'], suppressValidationErrors: options?.suppressValidationErrors },
        );

        // EXTEND TRANSPORT MODIFICATIONS HERE

        if (changeTransportResult.error !== undefined) {
            return null;
        }

        import('gtm/handlers/onGtmTransportChangeEventHandler').then(({ onGtmTransportChangeEventHandler }) => {
            onGtmTransportChangeEventHandler(
                gtmCart.current,
                changeTransportResult.data?.ChangeTransportInCart.transport ?? null,
                newPickupPlace,
                changeTransportResult.data?.ChangeTransportInCart.payment?.name,
                !canSeePrices,
            );
        });

        return changeTransportResult.data?.ChangeTransportInCart;
    };

    return { changeTransportInCart, isChangingTransportInCart };
};
