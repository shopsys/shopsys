import { useRouter } from 'next/router';
import { useEffect, useRef } from 'react';
import { DomainConfigType } from 'utils/domain/domainConfig';
import { getLocalePrefix } from 'utils/domain/domainUtils';
import { getGoPayPaymentSessionForOrder } from 'utils/goPayPaymentSessionStorage';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

// Browser back from external bank page (3DS) loses Next.js client-side query params.
// localStorage session is the backup to restore orderUuid + validity hash for the payment status page.
export const useGoPaySessionRecovery = (
    domainConfig: DomainConfigType,
    orderUuid: string,
    orderPaymentStatusPageValidityHashParam: string,
): void => {
    const router = useRouter();
    const wasSessionRecoveryAttemptedRef = useRef(false);

    useEffect(() => {
        if (wasSessionRecoveryAttemptedRef.current || orderPaymentStatusPageValidityHashParam) {
            return;
        }

        wasSessionRecoveryAttemptedRef.current = true;

        if (!orderUuid) {
            return;
        }

        const session = getGoPayPaymentSessionForOrder(domainConfig.url, orderUuid);

        if (!session) {
            return;
        }

        const [orderPaymentConfirmationUrl] = getInternationalizedStaticUrls(
            ['/order-payment-confirmation'],
            domainConfig.url,
        );
        const localePrefix = getLocalePrefix(domainConfig);

        router.replace({
            pathname: `${localePrefix}${orderPaymentConfirmationUrl}`,
            query: {
                orderIdentifier: session.orderUuid,
                ...(session.orderUrlHash ? { orderUrlHash: session.orderUrlHash } : {}),
                orderPaymentStatusPageValidityHash: session.orderPaymentStatusPageValidityHash,
            },
        });
    }, [domainConfig, orderPaymentStatusPageValidityHashParam, orderUuid, router]);
};
