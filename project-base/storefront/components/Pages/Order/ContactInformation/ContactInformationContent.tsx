import { OrderAction } from 'components/Blocks/OrderAction/OrderAction';
import { OrderContentWrapper } from 'components/Blocks/OrderContentWrapper/OrderContentWrapper';
import { Form, FormContentWrapper } from 'components/Forms/Form/Form';
import { TIDs } from 'cypress/tids';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { FormProvider } from 'react-hook-form';
import { useErrorPopup } from 'utils/forms/useErrorPopup';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { ContactInformationFormContent } from './ContactInformationFormContent';
import { useContactInformationForm, useContactInformationFormMeta } from './contactInformationFormMeta';
import { useContactInformationPageNavigation, useCreateOrder } from './contactInformationUtils';
import { ContactInformationSendOrderButton } from './FormBlocks/ContactInformationSendOrderButton';

export const ContactInformationContent: FC = () => {
    const { t } = useTranslation();
    const [formProviderMethods] = useContactInformationForm();
    const formMeta = useContactInformationFormMeta();
    const { goToPreviousStepFromContactInformationPage } = useContactInformationPageNavigation();
    const { createOrder } = useCreateOrder(formProviderMethods, formMeta);

    useErrorPopup(formProviderMethods, formMeta.fields, GtmMessageOriginType.contact_information_page);

    return (
        <OrderContentWrapper activeStep={3}>
            <h1 className="sr-only">{t('Contact information')}</h1>

            <FormProvider {...formProviderMethods}>
                <Form
                    preventEnterSubmission
                    formName={formMeta.formName}
                    tid={TIDs.contact_information_form}
                    onSubmit={formProviderMethods.handleSubmit(createOrder)}
                >
                    <FormContentWrapper>
                        <ContactInformationFormContent />

                        <ContactInformationSendOrderButton />

                        <OrderAction
                            backStepClickHandler={goToPreviousStepFromContactInformationPage}
                            buttonBack={t('Back')}
                            buttonNext={t('Submit order')}
                            hasDisabledCursor={!formProviderMethods.formState.isValid}
                            hasDisabledLook={!formProviderMethods.formState.isValid}
                        />
                    </FormContentWrapper>
                </Form>
            </FormProvider>
        </OrderContentWrapper>
    );
};
