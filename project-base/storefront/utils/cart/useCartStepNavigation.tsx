import { ErrorPopup } from 'components/Blocks/Popup/ErrorPopup';
import { getTransportAndPaymentValidationMessages } from 'components/Pages/Order/TransportAndPayment/transportAndPaymentUtils';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { useCallback } from 'react';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { useSessionStore } from 'store/useSessionStore';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { hasValidationErrors } from 'utils/errors/hasValidationErrors';

type StepClickHandler = (step: number, url: string, redirectPageType: PageType) => (currentStep: number) => void;

export const useCartStepNavigation = (): { handleStepClick: StepClickHandler; isSecondStepFilled: boolean } => {
    const router = useRouter();
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const { transport, pickupPlace, payment, paymentGoPayBankSwift } = useCurrentCart();
    const { t } = useTranslation();

    const validationMessages = getTransportAndPaymentValidationMessages(
        transport,
        pickupPlace,
        payment,
        paymentGoPayBankSwift,
        t,
    );

    const isSecondStepFilled = !hasValidationErrors(validationMessages);

    const handleStepClick: StepClickHandler = useCallback(
        (step: number, url: string, redirectPageType: PageType) => {
            return (currentStep: number) => {
                if (currentStep === step) {
                    return;
                }

                if (currentStep === 1 && step === 3 && !isSecondStepFilled) {
                    return;
                }

                if (step === 3 && (currentStep === 2 || isSecondStepFilled)) {
                    if (!isSecondStepFilled) {
                        updatePortalContent(
                            <ErrorPopup
                                fields={validationMessages}
                                gtmMessageOrigin={GtmMessageOriginType.transport_and_payment_page}
                            />,
                        );
                        return;
                    }
                }

                updatePageLoadingState({ isPageLoading: true, redirectPageType });
                router.push(url);
            };
        },
        [router, updatePageLoadingState, updatePortalContent, validationMessages, isSecondStepFilled],
    );

    return { handleStepClick, isSecondStepFilled };
};
