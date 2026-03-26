import { useInlinePaymentBackGuard } from 'components/Pages/Order/PaymentConfirmation/useInlinePaymentBackGuard';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { RefObject, useEffect, useRef } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isInternalGoPayReturnUrl } from './goPayCheckoutUtils';

type UseGoPayInlineCheckoutReturns = {
    isPaymentActiveRef: RefObject<boolean>;
    initCheckout: (gatewayUrl: string, onError: (message: string) => void) => void;
    isGoPayScriptLoaded: boolean;
};

export const useGoPayInlineCheckout = (orderUuid: string): UseGoPayInlineCheckoutReturns => {
    const { t } = useTranslation();
    const domainConfig = useDomainConfig();
    const isPaymentActiveRef = useRef(false);
    const retryTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);
    const { exitInlineFlow } = useInlinePaymentBackGuard({
        domainConfig,
        orderUuid,
        isPaymentActiveRef,
    });

    useEffect(() => {
        return () => {
            if (retryTimerRef.current !== null) {
                clearTimeout(retryTimerRef.current);
                retryTimerRef.current = null;
            }
        };
    }, []);

    const initCheckout = (gatewayUrl: string, onError: (message: string) => void, attempt = 0): void => {
        const gopay = typeof window !== 'undefined' ? (window as any)._gopay : undefined;

        if (gopay?.checkout) {
            try {
                isPaymentActiveRef.current = true;
                gopay.checkout({ gatewayUrl, inline: true }, (checkoutResult: { url?: string }) => {
                    if (!checkoutResult.url) {
                        exitInlineFlow();

                        return;
                    }

                    if (isInternalGoPayReturnUrl(checkoutResult.url)) {
                        // GoPay returned to our storefront. Take control of navigation via
                        // exitInlineFlow so the parent reloads with the FRESH validity hash
                        // from session — GoPay's own navigation (when it happens) uses the
                        // stale return_url baked in at PayOrder time, which prevents the
                        // payment status page validity window from refreshing on the second
                        // visit (e.g. after Show payment instruction → completion). Without
                        // this, useUpdatePaymentStatus → tryEmitPaymentEvent never runs and
                        // ec.payment is silently dropped. exitInlineFlow is idempotent
                        // (guarded by isPaymentActiveRef) so popstate dedupe still works.
                        exitInlineFlow();
                    }
                });
            } catch {
                isPaymentActiveRef.current = false;
                onError(t('Failed to initialize payment gateway. Please try again.'));
            }

            return;
        }

        if (attempt < 10) {
            retryTimerRef.current = setTimeout(
                () => initCheckout(gatewayUrl, onError, attempt + 1),
                Math.min(100 * (attempt + 1), 500),
            );

            return;
        }

        isPaymentActiveRef.current = false;
        onError(t('Payment gateway failed to load. Please refresh the page.'));
    };

    const isGoPayScriptLoaded = typeof window !== 'undefined' && !!(window as any)._gopay?.checkout;

    return {
        isPaymentActiveRef,
        initCheckout,
        isGoPayScriptLoaded,
    };
};
