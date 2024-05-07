import { useCartQuery } from 'graphql/requests/cart/queries/CartQuery.generated';
import { TypeListedStoreFragment } from 'graphql/requests/stores/fragments/ListedStoreFragment.generated';
import { TypeTransportWithAvailablePaymentsAndStoresFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsAndStoresFragment.generated';
import { Maybe } from 'graphql/types';
import { useEffect } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { useSessionStore } from 'store/useSessionStore';
import { CurrentCartType } from 'types/cart';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';

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

    const [{ data: fetchedCartData, fetching: isFetching }, fetchCart] = useCartQuery({
        variables: { cartUuid },
        pause: !isCartHydrated || !isWithCart || authLoading !== null,
        requestPolicy: fromCache ? 'cache-first' : 'network-only',
    });

    let cart = undefined;
    if (isCartHydrated) {
        if (isWithCart) {
            cart = fetchedCartData?.cart;
        } else {
            cart = null;
        }
    }

    return {
        fetchCart: () => {
            if (isWithCart) {
                fetchCart();
            }
        },
        cart,
        isCartFetchingOrUnavailable: cart === undefined || isFetching || !!authLoading,
        transport: cart?.transport ?? null,
        pickupPlace: getSelectedPickupPlace(cart?.transport, cart?.selectedPickupPlaceIdentifier, packeteryPickupPoint),
        payment: cart?.payment ?? null,
        paymentGoPayBankSwift: cart?.paymentGoPayBankSwift ?? null,
        promoCode: cart?.promoCode ?? null,
        roundingPrice: cart?.roundingPrice ?? null,
        modifications: cart?.modifications ?? null,
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
