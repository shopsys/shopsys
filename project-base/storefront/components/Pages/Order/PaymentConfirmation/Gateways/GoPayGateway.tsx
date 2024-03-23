import { SpinnerIcon } from 'components/Basic/Icon/IconsSvg';
import { Button } from 'components/Forms/Button/Button';
import { usePayOrderMutation } from 'graphql/requests/orders/mutations/PayOrderMutation.generated';
import { GoPayCreatePaymentSetup } from 'graphql/types';
import { showErrorMessage } from 'helpers/toasts';
import useTranslation from 'next-translate/useTranslation';
import Script from 'next/script';
import { useEffect, useRef, useState } from 'react';

type GoPayGatewayProps = {
    orderUuid: string;
    requiresAction?: boolean;
    isDisabled?: boolean;
    initialButtonText?: string;
};

export const GoPayGateway: FC<GoPayGatewayProps> = ({
    orderUuid,
    requiresAction,
    initialButtonText,
    className,
    isDisabled,
}) => {
    const [initiatedPaymentGate, setInitiatedPaymentGate] = useState(!requiresAction);
    const [goPayPaymentSetup, setGoPayPaymentSetup] = useState<GoPayCreatePaymentSetup | undefined>(undefined);
    const [, payOrder] = usePayOrderMutation();
    const { t } = useTranslation();
    const wasPaidRef = useRef(false);

    useEffect(() => {
        if (!wasPaidRef.current && initiatedPaymentGate) {
            payOrder({ orderUuid }).then((payOrderResult) => {
                if (payOrderResult.error?.graphQLErrors) {
                    for (const error of payOrderResult.error.graphQLErrors) {
                        showErrorMessage(error.message);
                    }

                    return;
                }

                setGoPayPaymentSetup(payOrderResult.data?.PayOrder.goPayCreatePaymentSetup ?? undefined);
            });
            wasPaidRef.current = true;
        }
    }, [initiatedPaymentGate]);

    const initGoPayCheckout = (gatewayUrl: string) => () => {
        // @ts-expect-error 3rd party function
        _gopay.checkout({
            gatewayUrl,
            inline: true,
        });
    };

    return (
        <>
            {initiatedPaymentGate && !!goPayPaymentSetup && (
                <Script
                    id="go-pay-embedded-js"
                    src={goPayPaymentSetup.embedJs}
                    onLoad={initGoPayCheckout(goPayPaymentSetup.gatewayUrl)}
                />
            )}
            {requiresAction && (
                <Button className={className} isDisabled={isDisabled} onClick={() => setInitiatedPaymentGate(true)}>
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
