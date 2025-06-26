import { useCurrentCart } from './useCurrentCart';
import { useAppConfig } from 'components/providers/AppConfigProvider';
import { useRouter } from 'next/router';
import { useEffect, useState } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export const useOrderPagesAccess = (page: 'transport-and-payment' | 'contact-information') => {
    const router = useRouter();
    const { cart, isCartFetchingOrUnavailable } = useCurrentCart();
    const authLoading = usePersistStore((s) => s.authLoading);
    const { url } = useAppConfig((appConfig) => appConfig.domainConfig);
    const [canContentBeDisplayed, setCanContentBeDisplayed] = useState<boolean | undefined>(undefined);
    const [cartUrl, transportAndPaymentUrl] = getInternationalizedStaticUrls(
        ['/cart', '/order/transport-and-payment'],
        url,
    );

    useEffect(() => {
        if (!isCartFetchingOrUnavailable) {
            if (cart === null || !cart?.items.length) {
                setCanContentBeDisplayed(false);
                router.replace(cartUrl);
            } else if (page === 'contact-information' && (!cart.transport || !cart.payment)) {
                setCanContentBeDisplayed(false);
                router.replace(transportAndPaymentUrl);
            } else {
                setCanContentBeDisplayed(true);
            }
        }
    }, [cart, isCartFetchingOrUnavailable, authLoading]);

    return canContentBeDisplayed;
};
