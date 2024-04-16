import { useCartQuery } from 'graphql/requests/cart/queries/CartQuery.generated';
import { TypeListedStoreFragment } from 'graphql/requests/stores/fragments/ListedStoreFragment.generated';
import { TypeTransportWithAvailablePaymentsAndStoresFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsAndStoresFragment.generated';
import { Maybe } from 'graphql/types';
import { usePersistStore } from 'store/usePersistStore';
import { CurrentCartType } from 'types/cart';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { isStoreHydrated } from 'utils/store/isStoreHydrated';

export const useCurrentCart = (fromCache = true): CurrentCartType => {
    const isUserLoggedIn = useIsUserLoggedIn();
    const authLoading = usePersistStore((s) => s.authLoading);
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const packeteryPickupPoint = usePersistStore((store) => store.packeteryPickupPoint);

    const isWithCart = isUserLoggedIn || !!cartUuid;

    const [{ data: fetchedCartData, fetching }, fetchCart] = useCartQuery({
        variables: { cartUuid },
        pause: !isWithCart || authLoading !== null,
        requestPolicy: fromCache ? 'cache-first' : 'network-only',
    });

    const cartData = isWithCart && fetchedCartData?.cart ? fetchedCartData.cart : null;

    return {
        fetchCart,
        isWithCart,
        cart: isStoreHydrated() ? cartData : undefined,
        transport: cartData?.transport ?? null,
        pickupPlace: getSelectedPickupPlace(
            cartData?.transport,
            cartData?.selectedPickupPlaceIdentifier,
            packeteryPickupPoint,
        ),
        payment: cartData?.payment ?? null,
        paymentGoPayBankSwift: cartData?.paymentGoPayBankSwift ?? null,
        promoCode: cartData?.promoCode ?? null,
        isFetching: fetching,
        modifications: cartData?.modifications ?? null,
        roundingPrice: cartData?.roundingPrice ?? null,
    };
};

const getSelectedPickupPlace = (
    transport: Maybe<TypeTransportWithAvailablePaymentsAndStoresFragment> | undefined,
    pickupPlaceIdentifier: string | null | undefined,
    packeteryPickupPoint: TypeListedStoreFragment | null,
): TypeListedStoreFragment | null => {
    if (!transport || !pickupPlaceIdentifier) {
        return null;
    }

    if (transport.transportType.code === 'packetery') {
        return packeteryPickupPoint;
    }

    const pickupPlace = transport.stores?.edges?.find(
        (pickupPlaceNode) => pickupPlaceNode?.node?.identifier === pickupPlaceIdentifier,
    );

    return pickupPlace?.node === undefined ? null : pickupPlace.node;
};
