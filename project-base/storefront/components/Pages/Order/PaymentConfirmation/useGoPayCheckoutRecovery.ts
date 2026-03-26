import { useEffect, useLayoutEffect, useState } from 'react';
import { DomainConfigType } from 'utils/domain/domainConfig';
import { buildPaymentConfirmationUrlFromSession, getGoPayPaymentSession } from 'utils/goPayPaymentSessionStorage';

const useIsomorphicLayoutEffect = typeof window === 'undefined' ? useEffect : useLayoutEffect;

// Redirects to /order-payment-confirmation when a GoPay session exists and the user
// navigated back (e.g. browser-back through GoPay iframe history) to a checkout page
// that has no order-specific recovery (/cart, /order/transport-and-payment, /order/contact-information).
export const useGoPayCheckoutRecovery = (domainConfig: DomainConfigType): boolean => {
    const [isRecovering, setIsRecovering] = useState(false);
    const [redirectUrl, setRedirectUrl] = useState<string | null>(null);

    useIsomorphicLayoutEffect(() => {
        if (typeof window === 'undefined') {
            return;
        }

        const navigationEntries = window.performance.getEntriesByType('navigation');
        const navigationEntry = navigationEntries[0];
        const navigationType =
            typeof navigationEntry === 'object' && 'type' in navigationEntry ? navigationEntry.type : undefined;

        if (navigationType !== 'back_forward') {
            return;
        }

        const session = getGoPayPaymentSession(domainConfig.url);

        if (!session) {
            return;
        }

        const url = buildPaymentConfirmationUrlFromSession(domainConfig, session.orderUuid);

        if (!url) {
            return;
        }

        setIsRecovering(true);
        setRedirectUrl(url);
    }, [domainConfig]);

    useEffect(() => {
        if (redirectUrl) {
            window.location.href = redirectUrl;
        }
    }, [redirectUrl]);

    return isRecovering;
};
