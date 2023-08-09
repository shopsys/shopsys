import { RegistrationAddress } from './RegistrationAddress';
import { RegistrationCompany } from './RegistrationCompany';
import { RegistrationPassword } from './RegistrationPassword';
import { RegistrationUser } from './RegistrationUser';
import { useRegistrationForm, useRegistrationFormMeta } from './registrationFormMeta';
import { Button } from 'components/Forms/Button/Button';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { Form } from 'components/Forms/Form/Form';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine';
import { showInfoMessage, showSuccessMessage } from 'helpers/visual/toasts';
import { SimpleLayout } from 'components/Layout/SimpleLayout/SimpleLayout';
import { BreadcrumbFragmentApi, useRegistrationMutationApi } from 'graphql/generated';
import { setTokensToCookies } from 'helpers/auth/tokens';
import { blurInput } from 'helpers/forms/blurInput';
import { clearForm } from 'helpers/forms/clearForm';
import { handleFormErrors } from 'helpers/forms/handleFormErrors';
import { onGtmSendFormEventHandler } from 'helpers/gtm/eventHandlers';
import { useErrorPopupVisibility } from 'hooks/forms/useErrorPopupVisibility';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import dynamic from 'next/dynamic';
import { useCallback } from 'react';
import { FormProvider, SubmitHandler, useWatch } from 'react-hook-form';
import { usePersistStore } from 'store/usePersistStore';
import { RegistrationFormType } from 'types/form';
import { GtmFormType, GtmMessageOriginType } from 'types/gtm/enums';

const ErrorPopup = dynamic(() => import('components/Forms/Lib/ErrorPopup').then((component) => component.ErrorPopup));

type RegistrationContentProps = {
    breadcrumbs: BreadcrumbFragmentApi[];
};

export const RegistrationContent: FC<RegistrationContentProps> = ({ breadcrumbs }) => {
    const t = useTypedTranslationFunction();
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
            <SimpleLayout heading={t('Registration')} breadcrumb={breadcrumbs}>
                <FormProvider {...formProviderMethods}>
                    <Form onSubmit={formProviderMethods.handleSubmit(onRegistrationHandler)}>
                        <div className="mb-10">
                            <div className="mb-6 -mr-4 flex w-full items-center justify-between rounded-l-xl bg-blueLight p-4">
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
                            name={formMeta.fields.gdprAgreement.name}
                            control={formProviderMethods.control}
                            formName={formMeta.formName}
                            render={(checkbox) => <ChoiceFormLine>{checkbox}</ChoiceFormLine>}
                            checkboxProps={{
                                label: formMeta.fields.gdprAgreement.label,
                            }}
                        />
                        <CheckboxControlled
                            name={formMeta.fields.newsletterSubscription.name}
                            control={formProviderMethods.control}
                            formName={formMeta.formName}
                            render={(checkbox) => <ChoiceFormLine>{checkbox}</ChoiceFormLine>}
                            checkboxProps={{
                                label: formMeta.fields.newsletterSubscription.label,
                            }}
                        />
                        <div className="mt-8 flex w-full justify-center">
                            <Button type="submit">{t('Sign up')}</Button>
                        </div>
                    </Form>
                </FormProvider>
            </SimpleLayout>

            {isErrorPopupVisible && (
                <ErrorPopup
                    onCloseCallback={() => setErrorPopupVisibility(false)}
                    fields={formMeta.fields}
                    gtmMessageOrigin={GtmMessageOriginType.other}
                />
            )}
        </>
    );
};
