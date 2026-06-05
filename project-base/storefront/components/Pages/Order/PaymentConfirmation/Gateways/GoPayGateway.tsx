import { SpinnerIcon } from 'components/Basic/Icon/SpinnerIcon';
import { Button } from 'components/Forms/Button/Button';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { getStaticRewritePathKeyType } from 'config/staticRewritePaths';
import { usePayOrderMutation } from 'graphql/requests/orders/mutations/PayOrderMutation.generated';
import { TypeGoPayCreatePaymentSetup } from 'graphql/types';
import { useRouter } from 'next/router';
import Script from 'next/script';
import { startTransition, useEffect, useEffectEvent, useRef, useState } from 'react';
import { getUserFriendlyErrors } from 'utils/errors/friendlyErrorMessageParser';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
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
    const { t } = useTranslation();
    const [, payOrder] = usePayOrderMutation();
    const { url: domainUrl } = useDomainConfig();
    const router = useRouter();

    const [initiatedPaymentGate, setInitiatedPaymentGate] = useState(false);
    const [isMaxTransactionCountReached, setIsMaxTransactionCountReached] = useState(false);
    const [goPayPaymentSetup, setGoPayPaymentSetup] = useState<TypeGoPayCreatePaymentSetup | undefined>(undefined);

    const autoTriggeredOrderRef = useRef<string | null>(null);

    const markPaymentActionRequired = async () => {
        const query = {
            ...router.query,
            requiresAction: true,
        };

        const staticPathname = getStaticRewritePathKeyType(router.pathname, domainUrl);

        if (staticPathname) {
            const [originalInternationalizedPathname] = getInternationalizedStaticUrls([staticPathname], domainUrl);
            await router.replace(
                {
                    pathname: originalInternationalizedPathname,
                    query,
                },
                undefined,
                { shallow: true },
            );
        }
    };

    const handlePayOrder = async () => {
        setInitiatedPaymentGate(true);

        const payOrderResult = await payOrder({ orderUuid });

        if (payOrderResult.error) {
            const parsedErrors = getUserFriendlyErrors(payOrderResult.error, t);

            if (parsedErrors.applicationError?.type === 'max-transaction-count-reached') {
                setIsMaxTransactionCountReached(true);
                setInitiatedPaymentGate(false);
                onMaxTransactionCountReached?.();
                return;
            }

            if (parsedErrors.applicationError) {
                showErrorMessage(parsedErrors.applicationError.message);
            }
            setInitiatedPaymentGate(false);
            return;
        }

        const goPayCreatePaymentSetup = payOrderResult.data?.PayOrder.goPayCreatePaymentSetup ?? undefined;

        if (goPayCreatePaymentSetup) {
            await markPaymentActionRequired();
        }

        setGoPayPaymentSetup(goPayCreatePaymentSetup);
    };

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
                setTimeout(
                    () => {
                        startTransition(() => {
                            attemptCheckout(attempt + 1);
                        });
                    },
                    Math.min(100 * (attempt + 1), 500),
                );
            } else {
                showErrorMessage(t('Payment gateway failed to load. Please refresh the page.'));
                setInitiatedPaymentGate(false);
            }
        };

        attemptCheckout();
    };

    const handlePaymentButtonClick = () => {
        handlePayOrder();
    };

    const handlePaymentError = () => {
        showErrorMessage(t('Failed to load payment gateway. Please try again.'));
        setInitiatedPaymentGate(false);
    };

    const onPayOrder = useEffectEvent(async () => {
        await handlePayOrder();
    });

    useEffect(() => {
        if (!requiresAction && autoTriggeredOrderRef.current !== orderUuid) {
            autoTriggeredOrderRef.current = orderUuid;
            onPayOrder();
        }
    }, [requiresAction, orderUuid]);

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
