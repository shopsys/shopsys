import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useCartQuery } from 'graphql/requests/cart/queries/CartQuery.generated';
import { useEffect } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { useSessionStore } from 'store/useSessionStore';
import { CurrentCartType } from 'types/cart';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { getSelectedPickupPlace } from 'utils/cart/pickupPlaceCalculations';

export const useCurrentCart = (fromCache = true): CurrentCartType => {
    const isUserLoggedIn = useIsUserLoggedIn();
    const authLoading = usePersistStore((s) => s.authLoading);
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const packeteryPickupPoint = usePersistStore((store) => store.packeteryPickupPoint);
    const isCartHydrated = useSessionStore((s) => s.isCartHydrated);
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);
    const isWithCart = isUserLoggedIn || !!cartUuid;
    const { canCreateOrder } = useAuthorization();

    useEffect(() => {
        updatePageLoadingState({ isCartHydrated: true });
    }, []);

    const [{ data: fetchedCartData, fetching: isCartFetching }, fetchCart] = useCartQuery({
        variables: { cartUuid },
        pause: !isCartHydrated || !isWithCart || authLoading !== null || !canCreateOrder,
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
        isCartFetchingOrUnavailable: cart === undefined || isCartFetching || !!authLoading,
        transport: cart?.transport ?? null,
        pickupPlace: getSelectedPickupPlace(cart?.transport, cart?.selectedPickupPlaceIdentifier, packeteryPickupPoint),
        payment: cart?.payment ?? null,
        paymentGoPayBankSwift: cart?.paymentGoPayBankSwift ?? null,
        promoCodes: cart?.promoCodes ?? [],
        roundingPrice: cart?.roundingPrice ?? null,
        modifications: cart?.modifications ?? null,
    };
};
