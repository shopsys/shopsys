import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { GtmCartInfoType } from 'gtm/types/objects';
import { usePersistStore } from 'store/usePersistStore';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { getGtmMappedCart } from './getGtmMappedCart';

export const useGtmCartInfo = (): {
    gtmCartInfo: GtmCartInfoType | null;
    isCartLoaded: boolean;
    pickupPlace: StoreOrPacketeryPoint | null;
} => {
    const { cart, promoCodes, isCartFetchingOrUnavailable, pickupPlace } = useCurrentCart();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const isUserLoggedIn = useIsUserLoggedIn();
    const domainConfig = useDomainConfig();

    if (!cart) {
        return { gtmCartInfo: null, isCartLoaded: !isCartFetchingOrUnavailable, pickupPlace };
    }

    return {
        gtmCartInfo: getGtmMappedCart(cart, promoCodes, isUserLoggedIn, domainConfig, cartUuid),
        isCartLoaded: !isCartFetchingOrUnavailable,
        pickupPlace,
    };
};
