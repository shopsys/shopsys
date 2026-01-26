import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { useChangeTransportInCartMutation } from 'graphql/requests/cart/mutations/ChangeTransportInCartMutation.generated';
import { useGtmCartInfo } from 'gtm/utils/useGtmCartInfo';
import { usePersistStore } from 'store/usePersistStore';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { useLatest } from 'utils/ui/useLatest';

export type ChangeTransportInCart = (
    newTransportUuid: string | null,
    newPickupPlace: StoreOrPacketeryPoint | null,
) => Promise<TypeCartFragment | undefined | null>;

export const useChangeTransportInCart = () => {
    const [{ fetching: isChangingTransportInCart }, changeTransportInCartMutation] = useChangeTransportInCartMutation();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const { gtmCartInfo } = useGtmCartInfo();
    const { canSeePrices } = useAuthorization();

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
