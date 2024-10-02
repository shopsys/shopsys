import { RegistrationAddress } from './RegistrationAddress';
import { RegistrationCompany } from './RegistrationCompany';
import { RegistrationPassword } from './RegistrationPassword';
import { RegistrationUser } from './RegistrationUser';
import { useRegistrationForm, useRegistrationFormMeta } from './registrationFormMeta';
import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper } from 'components/Forms/Form/Form';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
import { AnimatePresence } from 'framer-motion';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import useTranslation from 'next-translate/useTranslation';
import { FormProvider, SubmitHandler, useWatch } from 'react-hook-form';
import { usePersistStore } from 'store/usePersistStore';
import { RegistrationFormType } from 'types/form';
import { useRegistration } from 'utils/auth/useRegistration';
import { blurInput } from 'utils/forms/blurInput';
import { clearForm } from 'utils/forms/clearForm';
import { handleFormErrors } from 'utils/forms/handleFormErrors';
import { useErrorPopup } from 'utils/forms/useErrorPopup';

export const RegistrationContent: FC = () => {
    const { t } = useTranslation();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const [formProviderMethods, defaultValues] = useRegistrationForm();
    const formMeta = useRegistrationFormMeta(formProviderMethods);
    const { register } = useRegistration();

    useErrorPopup(formProviderMethods, formMeta.fields, undefined, GtmMessageOriginType.other);

    const onRegistrationHandler: SubmitHandler<RegistrationFormType> = async (registrationFormData) => {
        blurInput();
        const registrationError = await register({
            ...registrationFormData,
            password: registrationFormData.password,
            cartUuid,
            country: registrationFormData.country.value,
            companyCustomer: registrationFormData.customer === 'companyCustomer',
            billingAddressUuid: null,
        });

        handleFormErrors(registrationError, formProviderMethods, t, formMeta.messages.error);
        clearForm(registrationError, formProviderMethods, defaultValues);
    };

    const customerValue = useWatch({ name: formMeta.fields.customer.name, control: formProviderMethods.control });

    return (
        <Webline className="flex flex-col items-center">
            <h1 className="w-full max-w-3xl">{t('New customer registration')}</h1>
            <FormProvider {...formProviderMethods}>
                <Form
                    className="flex w-full max-w-3xl justify-center"
                    onSubmit={formProviderMethods.handleSubmit(onRegistrationHandler)}
                >
                    <FormContentWrapper>
                        <RegistrationUser />

                        <AnimatePresence initial={false}>
                            {customerValue === 'companyCustomer' && (
                                <AnimateCollapseDiv className="!flex flex-col" keyName="registration-company-data">
                                    <RegistrationCompany />
                                </AnimateCollapseDiv>
                            )}
                        </AnimatePresence>

                        <RegistrationPassword />

                        <RegistrationAddress />

                        <FormBlockWrapper>
                            <CheckboxControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.gdprAgreement.name}
                                render={(checkbox) => <ChoiceFormLine>{checkbox}</ChoiceFormLine>}
                                checkboxProps={{
                                    label: formMeta.fields.gdprAgreement.label,
                                }}
                            />
                            <CheckboxControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.newsletterSubscription.name}
                                render={(checkbox) => <ChoiceFormLine>{checkbox}</ChoiceFormLine>}
                                checkboxProps={{
                                    label: formMeta.fields.newsletterSubscription.label,
                                }}
                            />
                            <FormButtonWrapper>
                                <SubmitButton tid={TIDs.registration_submit_button}>{t('Sign up')}</SubmitButton>
                            </FormButtonWrapper>
                        </FormBlockWrapper>
                    </FormContentWrapper>
                </Form>
            </FormProvider>
        </Webline>
    );
};
