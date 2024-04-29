import {
    useCartQueryApi,
    Maybe,
    TransportWithAvailablePaymentsAndStoresFragmentApi,
    ListedStoreFragmentApi,
} from 'graphql/generated';
import { useIsUserLoggedIn } from 'hooks/auth/useIsUserLoggedIn';
import { useEffect } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { useSessionStore } from 'store/useSessionStore';
import { CurrentCartType } from 'types/cart';

export const useCurrentCart = (fromCache = true): CurrentCartType => {
    const isUserLoggedIn = useIsUserLoggedIn();
    const authLoading = usePersistStore((s) => s.authLoading);
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const packeteryPickupPoint = usePersistStore((store) => store.packeteryPickupPoint);
    const isCartHydrated = useSessionStore((s) => s.isCartHydrated);
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);

    const isWithCart = isUserLoggedIn || !!cartUuid;

    useEffect(() => {
        updatePageLoadingState({ isCartHydrated: true });
    }, []);

    const [{ data: fetchedCartData, fetching }, fetchCart] = useCartQueryApi({
        variables: { cartUuid },
        pause: !isCartHydrated || !isWithCart || authLoading !== null,
        requestPolicy: fromCache ? 'cache-first' : 'network-only',
    });

    const cartData = fetchedCartData?.cart ? fetchedCartData.cart : null;

    return {
        fetchCart,
        isCartHydrated,
        isWithCart,
        cart: cartData,
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
