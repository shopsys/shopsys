import { useEffect, useLayoutEffect, useState } from 'react';
import { DomainConfigType } from 'utils/domain/domainConfig';
import { buildPaymentConfirmationUrlFromSession } from 'utils/goPayPaymentSessionStorage';

const useIsomorphicLayoutEffect = typeof window === 'undefined' ? useEffect : useLayoutEffect;

export const useOrderDetailGoPayRecovery = (domainConfig: DomainConfigType, orderUuid?: string): boolean => {
    const [isRecoveringGoPaySession, setIsRecoveringGoPaySession] = useState(false);
    const [goPayRedirectUrl, setGoPayRedirectUrl] = useState<string | null>(null);

    useIsomorphicLayoutEffect(() => {
        if (typeof window === 'undefined' || !orderUuid) {
            return;
        }

        const navigationEntries = window.performance.getEntriesByType('navigation');
        const navigationEntry = navigationEntries[0];
        const navigationType =
            typeof navigationEntry === 'object' && 'type' in navigationEntry ? navigationEntry.type : undefined;

        if (navigationType !== 'back_forward') {
            return;
        }

        const redirectUrl = buildPaymentConfirmationUrlFromSession(domainConfig, orderUuid);

        if (!redirectUrl) {
            return;
        }

        setIsRecoveringGoPaySession(true);
        setGoPayRedirectUrl(redirectUrl);
    }, [domainConfig, orderUuid]);

    useEffect(() => {
        if (goPayRedirectUrl) {
            window.location.href = goPayRedirectUrl;
        }
    }, [goPayRedirectUrl]);

    return isRecoveringGoPaySession;
};
