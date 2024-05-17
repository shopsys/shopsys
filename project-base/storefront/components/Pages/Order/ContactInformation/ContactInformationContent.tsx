import { ContactInformationFormContent } from './ContactInformationFormContent';
import { ContactInformationEmail } from './FormBlocks/ContactInformationEmail';
import { ContactInformationSendOrderButton } from './FormBlocks/ContactInformationSendOrderButton';
import { useContactInformationForm, useContactInformationFormMeta } from './contactInformationFormMeta';
import { useContactInformationPageNavigation, useCreateOrder, useShouldDisplayContactInformationForm } from './utils';
import { OrderAction } from 'components/Blocks/OrderAction/OrderAction';
import { OrderContentWrapper } from 'components/Blocks/OrderContentWrapper/OrderContentWrapper';
import { Form } from 'components/Forms/Form/Form';
import { TIDs } from 'cypress/tids';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import useTranslation from 'next-translate/useTranslation';
import { FormProvider } from 'react-hook-form';
import { useErrorPopup } from 'utils/forms/useErrorPopup';

export const ContactInformationWrapper: FC = () => {
    const { t } = useTranslation();
    const [formProviderMethods] = useContactInformationForm();
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const shouldDisplayContactInformationForm = useShouldDisplayContactInformationForm(formProviderMethods, formMeta);
    const { goToPreviousStepFromContactInformationPage } = useContactInformationPageNavigation();
    const { createOrder, isCreatingOrder } = useCreateOrder(formProviderMethods, formMeta);

    useErrorPopup(formProviderMethods, formMeta.fields, undefined, GtmMessageOriginType.contact_information_page);

    return (
        <OrderContentWrapper activeStep={3}>
            <FormProvider {...formProviderMethods}>
                <Form tid={TIDs.contact_information_form} onSubmit={formProviderMethods.handleSubmit(createOrder)}>
                    <ContactInformationEmail />

                    {shouldDisplayContactInformationForm && <ContactInformationFormContent />}

                    <ContactInformationSendOrderButton />

                    <OrderAction
                        withGapBottom
                        backStepClickHandler={goToPreviousStepFromContactInformationPage}
                        buttonBack={t('Back')}
                        buttonNext={t('Submit order')}
                        hasDisabledLook={!formProviderMethods.formState.isValid}
                        isLoading={isCreatingOrder}
                        withGapTop={false}
                    />
                </Form>
            </FormProvider>
        </OrderContentWrapper>
    );
};
