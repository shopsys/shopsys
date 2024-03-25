import { RegistrationAddress } from './RegistrationAddress';
import { RegistrationCompany } from './RegistrationCompany';
import { RegistrationPassword } from './RegistrationPassword';
import { RegistrationUser } from './RegistrationUser';
import { useRegistrationForm, useRegistrationFormMeta } from './registrationFormMeta';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { Form } from 'components/Forms/Form/Form';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine';
import { SimpleLayout } from 'components/Layout/SimpleLayout/SimpleLayout';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { blurInput } from 'helpers/forms/blurInput';
import { clearForm } from 'helpers/forms/clearForm';
import { handleFormErrors } from 'helpers/forms/handleFormErrors';
import { useRegistration } from 'hooks/auth/useRegistration';
import { useErrorPopupVisibility } from 'hooks/forms/useErrorPopupVisibility';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { FormProvider, useWatch } from 'react-hook-form';
import { usePersistStore } from 'store/usePersistStore';
import { RegistrationFormType } from 'types/form';

const ErrorPopup = dynamic(() => import('components/Forms/Lib/ErrorPopup').then((component) => component.ErrorPopup));

export const RegistrationContent: FC = () => {
    const { t } = useTranslation();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const [formProviderMethods, defaultValues] = useRegistrationForm();
    const formMeta = useRegistrationFormMeta(formProviderMethods);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useErrorPopupVisibility(formProviderMethods);
    const register = useRegistration();

    const onRegistrationHandler = async (data: RegistrationFormType) => {
        blurInput();
        const registrationError = await register({
            ...data,
            password: data.passwordFirst,
            cartUuid,
            country: data.country.value,
            companyCustomer: data.customer === 'companyCustomer',
            lastOrderUuid: null,
        });

        handleFormErrors(registrationError, formProviderMethods, t, formMeta.messages.error);
        clearForm(registrationError, formProviderMethods, defaultValues);
    };

    const customerValue = useWatch({ name: formMeta.fields.customer.name, control: formProviderMethods.control });

    return (
        <>
            <SimpleLayout heading={t('Registration')}>
                <FormProvider {...formProviderMethods}>
                    <Form onSubmit={formProviderMethods.handleSubmit(onRegistrationHandler)}>
                        <div className="mb-10">
                            <div className="mb-6 -mr-4 flex w-full items-center justify-between rounded-l bg-blueLight p-4">
                                <div className="block text-lg text-primary md:pr-32 lg:text-xl">
                                    <div className="block text-xl font-semibold">
                                        {t("Don't have an account yet? Register.")}
                                    </div>
                                    {t('You will shop with us as')}:
                                </div>
                            </div>
                        </div>

                        <div className="mb-10">
                            <RegistrationUser />
                        </div>

                        {customerValue === 'companyCustomer' && (
                            <div className="mb-10">
                                <RegistrationCompany />
                            </div>
                        )}

                        <div className="mb-10">
                            <RegistrationPassword />
                        </div>

                        <div className="mb-10">
                            <RegistrationAddress />
                        </div>

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
                        <div className="mt-8 flex w-full justify-center">
                            <SubmitButton>{t('Sign up')}</SubmitButton>
                        </div>
                    </Form>
                </FormProvider>
            </SimpleLayout>

            {isErrorPopupVisible && (
                <ErrorPopup
                    fields={formMeta.fields}
                    gtmMessageOrigin={GtmMessageOriginType.other}
                    onCloseCallback={() => setErrorPopupVisibility(false)}
                />
            )}
        </>
    );
};
