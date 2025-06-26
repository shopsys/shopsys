import { getGtmMappedCart } from './getGtmMappedCart';
import { useAppConfig } from 'components/providers/AppConfigProvider';
import { GtmCartInfoType } from 'gtm/types/objects';
import { useMemo } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { useCurrentCart } from 'utils/cart/useCurrentCart';

export const useGtmCartInfo = (): { gtmCartInfo: GtmCartInfoType | null; isCartLoaded: boolean } => {
    const { cart, promoCodes, isCartFetchingOrUnavailable } = useCurrentCart();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const isUserLoggedIn = useIsUserLoggedIn();
    const domainConfig = useAppConfig((appConfig) => appConfig.domainConfig);

    return useMemo(() => {
        if (!cart) {
            return { gtmCartInfo: null, isCartLoaded: !isCartFetchingOrUnavailable };
        }

        return {
            gtmCartInfo: getGtmMappedCart(cart, promoCodes, isUserLoggedIn, domainConfig, cartUuid),
            isCartLoaded: !isCartFetchingOrUnavailable,
        };
    }, [cart, cartUuid, domainConfig, isCartFetchingOrUnavailable, isUserLoggedIn, promoCodes]);
};
