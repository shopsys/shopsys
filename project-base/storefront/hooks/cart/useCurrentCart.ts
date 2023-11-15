import {
    ListedStoreFragmentApi,
    Maybe,
    TransportWithAvailablePaymentsAndStoresFragmentApi,
    useCartQueryApi,
} from 'graphql/generated';
import { isStoreHydrated } from 'helpers/isStoreHydrated';
import { useIsUserLoggedIn } from 'hooks/auth/useIsUserLoggedIn';
import { usePersistStore } from 'store/usePersistStore';
import { CurrentCartType } from 'types/cart';

export const useCurrentCart = (fromCache = true): CurrentCartType => {
    const isUserLoggedIn = useIsUserLoggedIn();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const packeteryPickupPoint = usePersistStore((store) => store.packeteryPickupPoint);

    const isWithCart = isUserLoggedIn || !!cartUuid;

    const [{ data: fetchedCartData, fetching }, fetchCart] = useCartQueryApi({
        variables: { cartUuid },
        pause: !isWithCart,
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
    transport: Maybe<TransportWithAvailablePaymentsAndStoresFragmentApi> | undefined,
    pickupPlaceIdentifier: string | null | undefined,
    packeteryPickupPoint: ListedStoreFragmentApi | null,
): ListedStoreFragmentApi | null => {
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
