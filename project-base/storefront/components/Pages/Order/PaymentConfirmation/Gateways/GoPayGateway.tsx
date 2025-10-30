import { SpinnerIcon } from 'components/Basic/Icon/SpinnerIcon';
import { Button } from 'components/Forms/Button/Button';
import { usePayOrderMutation } from 'graphql/requests/orders/mutations/PayOrderMutation.generated';
import { TypeGoPayCreatePaymentSetup } from 'graphql/types';
import { useRouter } from 'next/router';
import Script from 'next/script';
import { useEffect, useRef, useState } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';

type GoPayGatewayProps = {
    orderUuid: string;
    requiresAction?: boolean;
    isDisabled?: boolean;
    initialButtonText?: string;
    onMaxTransactionCountReached?: () => void;
};

export const GoPayGateway: FC<GoPayGatewayProps> = ({
    orderUuid,
    requiresAction,
    initialButtonText,
    className,
    isDisabled,
    onMaxTransactionCountReached,
}) => {
    const [initiatedPaymentGate, setInitiatedPaymentGate] = useState(!requiresAction);
    const [isMaxTransactionCountReached, setIsMaxTransactionCountReached] = useState(false);
    const [goPayPaymentSetup, setGoPayPaymentSetup] = useState<TypeGoPayCreatePaymentSetup | undefined>(undefined);
    const router = useRouter();
    const [, payOrder] = usePayOrderMutation();
    const { t } = useTranslation();
    const wasPaidRef = useRef(false);

    const handleMaxTransactionCountReached = () => {
        setIsMaxTransactionCountReached(true);
        showErrorMessage(t('Max transaction count reached'));
        onMaxTransactionCountReached?.();
    };

    useEffect(() => {
        if (!wasPaidRef.current && initiatedPaymentGate) {
            const query = {
                ...router.query,
                requiresAction: true,
            };
            router
                .replace(
                    {
                        pathname: router.pathname,
                        query,
                    },
                    undefined,
                    { shallow: true },
                )
                .then(() => {
                    payOrder({ orderUuid }).then((payOrderResult) => {
                        if (payOrderResult.error?.graphQLErrors) {
                            for (const error of payOrderResult.error.graphQLErrors) {
                                const isMaxTransactionCountReached = error.message.includes(
                                    'Max transaction count reached',
                                );
                                if (isMaxTransactionCountReached) {
                                    handleMaxTransactionCountReached();
                                    return;
                                }
                                showErrorMessage(error.message);
                            }
                            setInitiatedPaymentGate(false);
                            return;
                        }

                        setGoPayPaymentSetup(payOrderResult.data?.PayOrder.goPayCreatePaymentSetup ?? undefined);
                    });
                });
            wasPaidRef.current = true;
        }
    }, [initiatedPaymentGate, orderUuid, router, payOrder, t]);

    const initGoPayCheckout = (gatewayUrl: string) => () => {
        const attemptCheckout = (attempt = 0) => {
            const gopayGlobal = typeof window !== 'undefined' ? (window as any)._gopay : undefined;
            if (gopayGlobal && typeof gopayGlobal.checkout === 'function') {
                try {
                    gopayGlobal.checkout({
                        gatewayUrl,
                        inline: true,
                    });
                } catch {
                    showErrorMessage(t('Failed to initialize payment gateway. Please try again.'));
                    setInitiatedPaymentGate(false);
                }
            } else if (attempt < 10) {
                setTimeout(() => attemptCheckout(attempt + 1), Math.min(100 * (attempt + 1), 500));
            } else {
                showErrorMessage(t('Payment gateway failed to load. Please refresh the page.'));
                setInitiatedPaymentGate(false);
            }
        };

        attemptCheckout();
    };

    const handlePaymentButtonClick = () => {
        setInitiatedPaymentGate(true);
    };

    const handlePaymentError = () => {
        showErrorMessage(t('Failed to load payment gateway. Please try again.'));
        setInitiatedPaymentGate(false);
    };

    return (
        <>
            {initiatedPaymentGate && !!goPayPaymentSetup && (
                <Script
                    id="go-pay-embedded-js"
                    src={goPayPaymentSetup.embedJs}
                    strategy="afterInteractive"
                    onError={handlePaymentError}
                    onLoad={initGoPayCheckout(goPayPaymentSetup.gatewayUrl)}
                />
            )}
            {requiresAction && !isMaxTransactionCountReached && (
                <Button
                    className={className}
                    hasDisabledLook={isDisabled}
                    size="xlarge"
                    onClick={handlePaymentButtonClick}
                >
                    {initiatedPaymentGate ? (
                        <>
                            <SpinnerIcon className="w-5" />
                            {t('You are being redirected...')}
                        </>
                    ) : (
                        initialButtonText
                    )}
                </Button>
            )}
        </>
    );
};
