import { Address } from './Address';
import { Company } from './Company';
import { useRegistrationForm, useRegistrationFormMeta } from './formMeta';
import { Password } from './Password';
import { User } from './User';
import { Button } from 'components/Forms/Button/Button';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { Form } from 'components/Forms/Form/Form';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine';
import { ErrorPopup } from 'components/Forms/Lib/ErrorPopup';
import { showInfoMessage, showSuccessMessage } from 'components/Helpers/toasts';
import { SimpleLayout } from 'components/Layout/SimpleLayout/SimpleLayout';
import { BreadcrumbFragmentApi, useRegistrationMutationApi } from 'graphql/generated';
import { setTokensToCookie } from 'helpers/auth/tokens';
import { blurInput } from 'helpers/forms/blurInput';
import { clearForm } from 'helpers/forms/clearForm';
import { handleFormErrors } from 'helpers/forms/handleFormErrors';
import { onGtmSendFormEventHandler } from 'helpers/gtm/eventHandlers';
import { useErrorPopupVisibility } from 'hooks/forms/useErrorPopupVisibility';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import Image from 'next/image';
import { useCallback } from 'react';
import { FormProvider, SubmitHandler, useWatch } from 'react-hook-form';
import { usePersistStore } from 'store/zustand/usePersistStore';
import { RegistrationFormType } from 'types/form';
import { GtmFormType, GtmMessageOriginType } from 'types/gtm/enums';

type RegistrationContentProps = {
    breadcrumbs: BreadcrumbFragmentApi[];
};

export const RegistrationContent: FC<RegistrationContentProps> = ({ breadcrumbs }) => {
    const t = useTypedTranslationFunction();
    const [, register] = useRegistrationMutationApi();
    const cartUuid = usePersistStore((s) => s.cartUuid);
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

                setTokensToCookie(accessToken, refreshToken);
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
                            <div className="relative my-6 -mr-4 w-full rounded-xl bg-blueLight p-4 lg:m-0">
                                <div className="block text-lg text-primary md:pr-32 lg:text-xl">
                                    <div className="block text-xl font-semibold">
                                        {t("Don't have an account yet? Register.")}
                                    </div>
                                    {t('You will shop with us as')}:
                                </div>
                                <div className="absolute right-5 bottom-0 hidden h-28 overflow-hidden md:right-10 md:block">
                                    <Image
                                        src="/images/qmark.png"
                                        height={120}
                                        width={75}
                                        alt={t("Don't have an account yet? Register.")}
                                        className="max-w-none"
                                    />
                                </div>
                            </div>
                        </div>

                        <div className="mb-10">
                            <User />
                        </div>

                        {customerValue === 'companyCustomer' && (
                            <div className="mb-10">
                                <Company />
                            </div>
                        )}

                        <div className="mb-10">
                            <Password />
                        </div>

                        <div className="mb-10">
                            <Address />
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

            <ErrorPopup
                isVisible={isErrorPopupVisible}
                onCloseCallback={() => setErrorPopupVisibility(false)}
                fields={formMeta.fields}
                gtmMessageOrigin={GtmMessageOriginType.other}
            />
        </>
    );
};
