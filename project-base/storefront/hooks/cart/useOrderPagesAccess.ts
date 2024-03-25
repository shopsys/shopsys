import { useCurrentCart } from './useCurrentCart';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { getInternationalizedStaticUrls } from 'helpers/staticUrls/getInternationalizedStaticUrls';
import { useRouter } from 'next/router';
import { useEffect, useState } from 'react';

export const useOrderPagesAccess = (page: 'transport-and-payment' | 'contact-information') => {
    const router = useRouter();
    const { cart, isFetching } = useCurrentCart();
    const { url } = useDomainConfig();
    const [canContentBeDisplayed, setCanContentBeDisplayed] = useState<boolean | undefined>(undefined);
    const [cartUrl, transportAndPaymentUrl] = getInternationalizedStaticUrls(
        ['/cart', '/order/transport-and-payment'],
        url,
    );

    useEffect(() => {
        if (cart !== undefined && !isFetching) {
            if (cart === null || !cart.items.length) {
                setCanContentBeDisplayed(false);
                router.replace(cartUrl);
            } else if (page === 'contact-information' && (!cart.transport || !cart.payment)) {
                setCanContentBeDisplayed(false);
                router.replace(transportAndPaymentUrl);
            } else {
                setCanContentBeDisplayed(true);
            }
        }
    }, [cart, isFetching]);

    return canContentBeDisplayed;
};
