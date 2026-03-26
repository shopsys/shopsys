import { Button } from 'components/Forms/Button/Button';
import { PaymentVerificationLoader } from 'components/Pages/Order/PaymentConfirmation/PaymentVerificationLoader';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { usePayOrderMutation } from 'graphql/requests/orders/mutations/PayOrderMutation.generated';
import { TypeGoPayCreatePaymentSetup } from 'graphql/types';
import {
    removeGtmPendingPaymentFromLocalStorage,
    saveGtmPendingPaymentInLocalStorage,
} from 'gtm/utils/gtmPaymentEventLocalStorage';
import Script from 'next/script';
import { useEffect, useRef, useState } from 'react';
import { getUserFriendlyErrors } from 'utils/errors/friendlyErrorMessageParser';
import {
    removeGoPayPaymentSession,
    saveGoPayPaymentSession,
    shouldOpenGoPayAsRedirectOnly,
} from 'utils/goPayPaymentSessionStorage';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { useGoPayInlineCheckout } from './useGoPayInlineCheckout';

type GoPayGatewayProps = {
    orderUuid: string;
    orderNumber?: string;
    paymentName?: string;
    paymentTransactionsCount?: number;
    requiresAction?: boolean;
    className?: string;
    isDisabled?: boolean;
    initialButtonText?: string;
    orderUrlHash?: string;
    onMaxTransactionCountReached?: () => void;
};

export const GoPayGateway: FC<GoPayGatewayProps> = ({
    orderUuid,
    orderNumber,
    paymentName,
    paymentTransactionsCount,
    requiresAction,
    initialButtonText,
    className,
    isDisabled,
    orderUrlHash,
    onMaxTransactionCountReached,
}) => {
    const { t } = useTranslation();
    const [, payOrder] = usePayOrderMutation();
    const { url: domainUrl } = useDomainConfig();

    const [isLoading, setIsLoading] = useState(false);
    const [goPaySetup, setGoPaySetup] = useState<TypeGoPayCreatePaymentSetup>();

    const autoTriggeredRef = useRef<string | null>(null);
    const { isPaymentActiveRef, initCheckout, isGoPayScriptLoaded } = useGoPayInlineCheckout(orderUuid);

    const handleError = (message: string): void => {
        showErrorMessage(message);
        removeGtmPendingPaymentFromLocalStorage();
        removeGoPayPaymentSession();
        setIsLoading(false);
        isPaymentActiveRef.current = false;
        setGoPaySetup(undefined);
    };

    const handlePayOrder = async (): Promise<void> => {
        if (isLoading) {
            return;
        }

        setIsLoading(true);

        // Mark URL as requiresAction so page refresh shows button instead of auto-triggering.
        // Uses history.replaceState directly to avoid Next.js page transition overhead.
        const url = new URL(window.location.href);
        url.searchParams.set('requiresAction', 'true');
        window.history.replaceState(window.history.state, '', url.toString());

        const result = await payOrder({ orderUuid });

        if (result.error) {
            const parsed = getUserFriendlyErrors(result.error, t);

            if (parsed.applicationError?.type === 'max-transaction-count-reached') {
                setIsLoading(false);
                isPaymentActiveRef.current = false;
                onMaxTransactionCountReached?.();

                return;
            }

            showErrorMessage(parsed.applicationError?.message ?? t('An error occurred while processing payment'));
            setIsLoading(false);
            isPaymentActiveRef.current = false;

            return;
        }

        const setup = result.data?.PayOrder.goPayCreatePaymentSetup;
        const validityHash = result.data?.PayOrder.orderPaymentStatusPageValidityHash;
        const shouldForceRedirect = shouldOpenGoPayAsRedirectOnly(domainUrl, orderUuid);

        if (setup && validityHash) {
            saveGoPayPaymentSession({
                orderUuid,
                orderUrlHash,
                orderPaymentStatusPageValidityHash: validityHash,
                domainUrl,
                forceRedirectAfterInlineReturn: shouldForceRedirect,
            });
        }

        if (setup && orderNumber && paymentName) {
            saveGtmPendingPaymentInLocalStorage({
                orderUuid,
                orderNumber,
                paymentName,
                paymentTransactionsCount:
                    paymentTransactionsCount !== undefined ? paymentTransactionsCount + 1 : undefined,
                domainUrl,
            });
        }

        if (setup && shouldForceRedirect) {
            isPaymentActiveRef.current = false;
            window.location.assign(setup.gatewayUrl);

            return;
        }

        setGoPaySetup(setup ?? undefined);

        if (setup && (window as any)._gopay?.checkout) {
            initCheckout(setup.gatewayUrl, handleError);
        }
    };

    // Auto-trigger payment on first mount (when not requiresAction).
    // Check URL directly as fallback — history.replaceState (used in handlePayOrder) does not update router.query.
    useEffect(() => {
        const urlHasRequiresAction =
            typeof window !== 'undefined' && new URL(window.location.href).searchParams.has('requiresAction');

        if (!requiresAction && !urlHasRequiresAction && autoTriggeredRef.current !== orderUuid) {
            autoTriggeredRef.current = orderUuid;
            void handlePayOrder();
        }
    }, [orderUuid, requiresAction]); // eslint-disable-line react-hooks/exhaustive-deps

    return (
        <>
            {isLoading && goPaySetup && !isGoPayScriptLoaded && (
                <Script
                    id="go-pay-embedded-js"
                    src={goPaySetup.embedJs}
                    strategy="afterInteractive"
                    onError={() => handleError(t('Failed to load payment gateway. Please try again.'))}
                    onLoad={() => initCheckout(goPaySetup.gatewayUrl, handleError)}
                />
            )}

            {isLoading && (
                <PaymentVerificationLoader
                    heading={t('Preparing your payment...')}
                    subtitle={t('Setting up your payment gateway. This will only take a moment.')}
                />
            )}

            {requiresAction && !isLoading && (
                <Button
                    className={className}
                    hasDisabledLook={isDisabled}
                    size="xlarge"
                    onClick={() => void handlePayOrder()}
                >
                    {initialButtonText}
                </Button>
            )}
        </>
    );
};
