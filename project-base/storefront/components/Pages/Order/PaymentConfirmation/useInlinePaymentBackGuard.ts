import { RefObject, useCallback, useEffect } from 'react';
import { DomainConfigType } from 'utils/domain/domainConfig';
import {
    buildPaymentConfirmationUrlFromSession,
    markGoPayPaymentSessionForRedirectOnly,
} from 'utils/goPayPaymentSessionStorage';

// Handles navigation events (popstate, pageshow) during active inline GoPay payment.
// Browser back from within GoPay's cross-origin iframe is NOT interceptable — the iframe
// creates its own history entries, so back first traverses iframe history before reaching
// our page. Session-based recovery on other pages ensures the user always reaches
// /order-payment-confirmation regardless of how they navigate away.
type UseInlinePaymentBackGuardProps = {
    domainConfig: DomainConfigType;
    orderUuid: string;
    isPaymentActiveRef: RefObject<boolean>;
};

type UseInlinePaymentBackGuardReturns = {
    exitInlineFlow: () => void;
};

export const useInlinePaymentBackGuard = ({
    domainConfig,
    orderUuid,
    isPaymentActiveRef,
}: UseInlinePaymentBackGuardProps): UseInlinePaymentBackGuardReturns => {
    const exitInlineFlow = useCallback((): void => {
        if (!isPaymentActiveRef.current) {
            return;
        }

        isPaymentActiveRef.current = false;
        markGoPayPaymentSessionForRedirectOnly(domainConfig.url, orderUuid);

        const redirectUrl = buildPaymentConfirmationUrlFromSession(domainConfig, orderUuid);

        if (redirectUrl) {
            window.location.replace(redirectUrl);

            return;
        }

        window.location.reload();
    }, [domainConfig, isPaymentActiveRef, orderUuid]);

    useEffect(() => {
        const onPopState = (): void => {
            if (isPaymentActiveRef.current) {
                exitInlineFlow();
            }
        };

        const onPageShow = (event: PageTransitionEvent): void => {
            if (!event.persisted || !isPaymentActiveRef.current) {
                return;
            }

            if (!buildPaymentConfirmationUrlFromSession(domainConfig, orderUuid)) {
                return;
            }

            exitInlineFlow();
        };

        window.addEventListener('popstate', onPopState);
        window.addEventListener('pageshow', onPageShow);

        return () => {
            window.removeEventListener('popstate', onPopState);
            window.removeEventListener('pageshow', onPageShow);
        };
    }, [domainConfig, exitInlineFlow, isPaymentActiveRef, orderUuid]);

    return {
        exitInlineFlow,
    };
};
