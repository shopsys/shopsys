'use client';

import { useAppConfig } from 'components/providers/AppConfigProvider';
import { useRouter } from 'next/router';
import { useSessionStore } from 'store/useSessionStore';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export const useCartPageNavigation = () => {
    const { url } = useAppConfig((appConfig) => appConfig.domainConfig);
    const router = useRouter();
    const [transportAndPaymentUrl] = getInternationalizedStaticUrls(['/order/transport-and-payment'], url);
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);

    const goToPreviousStepFromCartPage = () => {
        updatePageLoadingState({ isPageLoading: true, redirectPageType: 'homepage' });
        router.back();
    };

    const goToNextStepFromCartPage = () => {
        updatePageLoadingState({ isPageLoading: true, redirectPageType: 'transport-and-payment' });
        router.push(transportAndPaymentUrl);
    };

    return { goToPreviousStepFromCartPage, goToNextStepFromCartPage };
};
