import { getGtmMappedCart } from './getGtmMappedCart';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { GtmCartInfoType } from 'gtm/types/objects';
import { useIsUserLoggedIn } from 'hooks/auth/useIsUserLoggedIn';
import { useCurrentCart } from 'hooks/cart/useCurrentCart';
import { useMemo } from 'react';
import { usePersistStore } from 'store/usePersistStore';

export const useGtmCartInfo = (): { gtmCartInfo: GtmCartInfoType | null; isCartLoaded: boolean } => {
    const { cart, promoCode, isFetching } = useCurrentCart();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const isUserLoggedIn = useIsUserLoggedIn();
    const domainConfig = useDomainConfig();

    return useMemo(() => {
        if (!cart) {
            return { gtmCartInfo: null, isCartLoaded: !isFetching };
        }

        return {
            gtmCartInfo: getGtmMappedCart(cart, promoCode, isUserLoggedIn, domainConfig, cartUuid),
            isCartLoaded: !isFetching,
        };
    }, [cart, cartUuid, domainConfig, isFetching, isUserLoggedIn, promoCode]);
};
