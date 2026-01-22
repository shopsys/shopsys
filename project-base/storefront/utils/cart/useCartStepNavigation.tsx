import { ErrorPopup } from 'components/Blocks/Popup/ErrorPopup';
import { getTransportAndPaymentValidationMessages } from 'components/Pages/Order/TransportAndPayment/transportAndPaymentUtils';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { useRouter } from 'next/router';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { useSessionStore } from 'store/useSessionStore';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { hasValidationErrors } from 'utils/errors/hasValidationErrors';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type StepClickHandler = (step: number, url: string, redirectPageType: PageType) => (currentStep: number) => void;

export const useCartStepNavigation = (): { handleStepClick: StepClickHandler; isSecondStepFilled: boolean } => {
    const router = useRouter();
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const { transport, pickupPlace, payment } = useCurrentCart();
    const { t } = useTranslation();

    const validationMessages = getTransportAndPaymentValidationMessages(transport, pickupPlace, payment, t);

    const isSecondStepFilled = !hasValidationErrors(validationMessages);

    const handleStepClick: StepClickHandler = (step: number, url: string, redirectPageType: PageType) => {
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
    };

    return { handleStepClick, isSecondStepFilled };
};
