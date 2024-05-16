import { ContactInformationFormMetaType } from './contactInformationFormMeta';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useRouter } from 'next/router';
import { UseFormReturn, useWatch } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { useSessionStore } from 'store/useSessionStore';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export const useContactInformationPageNavigation = () => {
    const { url } = useDomainConfig();
    const router = useRouter();
    const [transportAndPaymentUrl] = getInternationalizedStaticUrls(['/order/transport-and-payment'], url);
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);

    const goToPreviousStepFromContactInformationPage = () => {
        updatePageLoadingState({ isPageLoading: true, redirectPageType: 'order-process' });
        router.push(transportAndPaymentUrl);
    };

    return { goToPreviousStepFromContactInformationPage };
};

export const useShouldDisplayContactInformationForm = (
    formProviderMethods: UseFormReturn<ContactInformation>,
    formMeta: ContactInformationFormMetaType,
) => {
    const emailValue = useWatch({ name: formMeta.fields.email.name, control: formProviderMethods.control });
    const isEmailFilledCorrectly = !!emailValue && !formProviderMethods.formState.errors.email;

    return isEmailFilledCorrectly;
};
