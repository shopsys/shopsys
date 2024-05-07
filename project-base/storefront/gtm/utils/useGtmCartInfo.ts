import { getGtmMappedCart } from './getGtmMappedCart';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { GtmCartInfoType } from 'gtm/types/objects';
import { useMemo } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { useCurrentCart } from 'utils/cart/useCurrentCart';

export const useGtmCartInfo = (): { gtmCartInfo: GtmCartInfoType | null; isCartLoaded: boolean } => {
    const { cart, promoCode, isCartFetchingOrUnavailable } = useCurrentCart();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const isUserLoggedIn = useIsUserLoggedIn();
    const domainConfig = useDomainConfig();

    return useMemo(() => {
        if (!cart) {
            return { gtmCartInfo: null, isCartLoaded: !isCartFetchingOrUnavailable };
        }

        return {
            gtmCartInfo: getGtmMappedCart(cart, promoCode, isUserLoggedIn, domainConfig, cartUuid),
            isCartLoaded: !isCartFetchingOrUnavailable,
        };
    }, [cart, cartUuid, domainConfig, isCartFetchingOrUnavailable, isUserLoggedIn, promoCode]);
};
