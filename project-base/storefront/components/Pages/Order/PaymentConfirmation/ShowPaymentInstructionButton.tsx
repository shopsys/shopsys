import { Button } from 'components/Forms/Button/Button';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useSetOrderPaymentStatusPageValidityHashMutation } from 'graphql/requests/orders/mutations/SetOrderPaymentStatusPageValidityHashMutation.generated';
import Script from 'next/script';
import { useState } from 'react';
import {
    removeGoPayPaymentSession,
    saveGoPayPaymentSession,
    shouldOpenGoPayAsRedirectOnly,
} from 'utils/goPayPaymentSessionStorage';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { useGoPayInlineCheckout } from './Gateways/useGoPayInlineCheckout';

type ShowPaymentInstructionButtonProps = {
    href: string;
    orderUuid: string;
    orderUrlHash?: string;
};

export const ShowPaymentInstructionButton: FC<ShowPaymentInstructionButtonProps> = ({
    href,
    orderUuid,
    orderUrlHash,
}) => {
    const { t } = useTranslation();
    const { url: domainUrl } = useDomainConfig();
    const [, setOrderPaymentStatusPageValidityHashMutation] = useSetOrderPaymentStatusPageValidityHashMutation();
    const [goPayEmbedJs, setGoPayEmbedJs] = useState<string | undefined>(undefined);
    const [isLoading, setIsLoading] = useState(false);
    const { isPaymentActiveRef, initCheckout, isGoPayScriptLoaded } = useGoPayInlineCheckout(orderUuid);

    const handleError = (message: string): void => {
        showErrorMessage(message);
        removeGoPayPaymentSession();
        isPaymentActiveRef.current = false;
        setIsLoading(false);
        setGoPayEmbedJs(undefined);
    };

    const handleShowPaymentInstruction = () => {
        setIsLoading(true);
        setOrderPaymentStatusPageValidityHashMutation({ orderUuid })
            .then((result) => {
                const paymentInstructionSetup = result.data?.SetOrderPaymentStatusPageValidityHashMutation;
                const validityHash = paymentInstructionSetup?.orderPaymentStatusPageValidityHash;

                if (!paymentInstructionSetup?.goPayEmbedJs || !validityHash) {
                    throw new Error('Missing GoPay payment instruction setup data');
                }

                const shouldForceRedirect = shouldOpenGoPayAsRedirectOnly(domainUrl, orderUuid);

                saveGoPayPaymentSession({
                    orderUuid,
                    orderUrlHash,
                    orderPaymentStatusPageValidityHash: validityHash,
                    domainUrl,
                    forceRedirectAfterInlineReturn: shouldForceRedirect,
                });

                if (shouldForceRedirect) {
                    isPaymentActiveRef.current = false;
                    window.location.assign(href);

                    return;
                }

                setGoPayEmbedJs(paymentInstructionSetup.goPayEmbedJs);

                if ((window as any)._gopay?.checkout) {
                    initCheckout(href, handleError);
                }
            })
            .catch(() => {
                handleError(t('Failed to load payment instruction. Please try again.'));
            });
    };

    return (
        <>
            {goPayEmbedJs && isLoading && !isGoPayScriptLoaded && (
                <Script
                    id="go-pay-embedded-js"
                    src={goPayEmbedJs}
                    onError={() => handleError(t('Failed to load payment gateway. Please try again.'))}
                    onLoad={() => initCheckout(href, handleError)}
                />
            )}
            <Button disabled={isLoading} onClick={handleShowPaymentInstruction}>
                {t('Show payment instruction')}
            </Button>
        </>
    );
};
