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
import { useRegistrationMutationApi } from 'graphql/generated';
import { onGtmSendFormEventHandler } from 'gtm/helpers/eventHandlers';
import { GtmFormType, GtmMessageOriginType } from 'gtm/types/enums';
import { setTokensToCookies } from 'helpers/auth/tokens';
import { blurInput } from 'helpers/forms/blurInput';
import { clearForm } from 'helpers/forms/clearForm';
import { handleFormErrors } from 'helpers/forms/handleFormErrors';
import { showInfoMessage, showSuccessMessage } from 'helpers/toasts';
import { useErrorPopupVisibility } from 'hooks/forms/useErrorPopupVisibility';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useCallback } from 'react';
import { FormProvider, SubmitHandler, useWatch } from 'react-hook-form';
import { usePersistStore } from 'store/usePersistStore';
import { RegistrationFormType } from 'types/form';

const ErrorPopup = dynamic(() => import('components/Forms/Lib/ErrorPopup').then((component) => component.ErrorPopup));

export const RegistrationContent: FC = () => {
    const { t } = useTranslation();
    const [, register] = useRegistrationMutationApi();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const [formProviderMethods, defaultValues] = useRegistrationForm();
    const formMeta = useRegistrationFormMeta(formProviderMethods);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useErrorPopupVisibility(formProviderMethods);

    const onRegistrationHandler = useCallback<SubmitHandler<RegistrationFormType>>(
        async (data) => {
            blurInput();
            const registerResult = await register({
                ...data,
                password: data.passwordFirst,
                previousCartUuid: cartUuid,
                country: data.country.value,
                companyCustomer: data.customer === 'companyCustomer',
                lastOrderUuid: null,
            });

            if (registerResult.data?.Register !== undefined) {
                const accessToken = registerResult.data.Register.tokens.accessToken;
                const refreshToken = registerResult.data.Register.tokens.refreshToken;

                setTokensToCookies(accessToken, refreshToken);
                showSuccessMessage(formMeta.messages.successAndLogged);

                if (registerResult.data.Register.showCartMergeInfo === true) {
                    showInfoMessage(t('Your cart has been modified. Please check the changes.'));
                }
                onGtmSendFormEventHandler(GtmFormType.registration);

                window.location.href = '/';
            }

            handleFormErrors(registerResult.error, formProviderMethods, t, formMeta.messages.error);
            clearForm(registerResult.error, formProviderMethods, defaultValues);
        },
        [cartUuid, formMeta.messages, formProviderMethods, register, t, defaultValues],
    );

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
