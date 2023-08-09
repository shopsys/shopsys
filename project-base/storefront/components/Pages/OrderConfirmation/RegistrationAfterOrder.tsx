import { useRegistrationAfterOrderForm, useRegistrationAfterOrderFormMeta } from './registrationAfterOrderFormMeta';
import { Heading } from 'components/Basic/Heading/Heading';
import { Button } from 'components/Forms/Button/Button';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { Form } from 'components/Forms/Form/Form';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { PasswordInputControlled } from 'components/Forms/TextInput/PasswordInputControlled';
import { showErrorMessage, showSuccessMessage } from 'components/Helpers/toasts';
import { Webline } from 'components/Layout/Webline/Webline';
import { useRegistrationMutationApi } from 'graphql/generated';
import { setTokensToCookie } from 'helpers/auth/tokens';
import { getUserFriendlyErrors } from 'helpers/errors/friendlyErrorMessageParser';
import { onGtmSendFormEventHandler } from 'helpers/gtm/eventHandlers';
import { useErrorPopupVisibility } from 'hooks/forms/useErrorPopupVisibility';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useCurrentUserContactInformation } from 'hooks/user/useCurrentUserContactInformation';
import Trans from 'next-translate/Trans';
import dynamic from 'next/dynamic';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { RegistrationAfterOrderFormType } from 'types/form';
import { GtmFormType, GtmMessageOriginType } from 'types/gtm/enums';

const ErrorPopup = dynamic(() => import('components/Forms/Lib/ErrorPopup').then((component) => component.ErrorPopup));

const TEST_IDENTIFIER = 'pages-orderconfirmation-registration-create-account';

type RegistrationAfterOrderProps = {
    lastOrderUuid: string;
};

export const RegistrationAfterOrder: FC<RegistrationAfterOrderProps> = ({ lastOrderUuid }) => {
    const contactInformation = useCurrentUserContactInformation();
    const [, register] = useRegistrationMutationApi();
    const t = useTypedTranslationFunction();
    const [formProviderMethods] = useRegistrationAfterOrderForm();
    const formMeta = useRegistrationAfterOrderFormMeta(formProviderMethods);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useErrorPopupVisibility(formProviderMethods);

    const onRegistrationSubmitHandler: SubmitHandler<RegistrationAfterOrderFormType> = async (data) => {
        const registerResult = await register({
            ...data,
            ...contactInformation,
            country: contactInformation.country.value,
            companyCustomer: contactInformation.customer === 'companyCustomer',
            previousCartUuid: null,
            lastOrderUuid,
        });

        if (registerResult.data !== undefined && registerResult.error === undefined) {
            const accessToken = registerResult.data.Register.tokens.accessToken;
            const refreshToken = registerResult.data.Register.tokens.refreshToken;

            setTokensToCookie(accessToken, refreshToken);
            showSuccessMessage(t('Your account has been created and you are logged in now'));
            onGtmSendFormEventHandler(GtmFormType.registration);

            window.location.href = '/';
        } else if (registerResult.error !== undefined) {
            const validationErrors = getUserFriendlyErrors(registerResult.error, t).userError?.validation;
            for (const fieldName in validationErrors) {
                showErrorMessage(validationErrors[fieldName].message, GtmMessageOriginType.order_confirmation_page);
            }
        }
    };

    return (
        <>
            <Webline>
                <div className="relative mb-20 flex flex-col rounded-xl border-2 border-primary before:absolute before:bottom-0 before:left-1/2 before:top-0 before:hidden before:w-1 before:-translate-x-1/2 before:bg-primary before:content-none lg:flex-row before:lg:block">
                    <div className="w-full p-5 lg:w-1/2 lg:py-8 lg:px-10">
                        <Heading type="h2" className="mb-5 text-4xl leading-10 [&>strong]:text-primary">
                            <Trans
                                i18nKey="Finish registration to loyalty program."
                                components={{ 0: <br />, 1: <strong /> }}
                            />
                        </Heading>
                        <ul>
                            {[
                                t('You will have an overview of your orders and complaints'),
                                t('Collecting points with every order'),
                                t('Possibility of purchases for better prices'),
                                t('Exclusive products as a part of the loyalty program'),
                            ].map((text) => (
                                <li
                                    key={text}
                                    className="relative mb-3 pl-4 leading-5 before:absolute before:left-0 before:top-2 before:h-1 before:w-1 before:rounded-full before:bg-primary before:content-none"
                                >
                                    {text}
                                </li>
                            ))}
                        </ul>
                    </div>
                    <div className="flex w-full flex-col items-center justify-center p-5 lg:w-1/2 lg:px-10 lg:py-8">
                        <div className="w-full lg:max-w-sm">
                            <Form onSubmit={formProviderMethods.handleSubmit(onRegistrationSubmitHandler)}>
                                <FormProvider {...formProviderMethods}>
                                    <PasswordInputControlled
                                        control={formProviderMethods.control}
                                        name={formMeta.fields.password.name}
                                        render={(passwordInput) => (
                                            <div className="mb-7">
                                                <FormLine>{passwordInput}</FormLine>
                                            </div>
                                        )}
                                        formName={formMeta.formName}
                                        passwordInputProps={{
                                            label: formMeta.fields.password.label,
                                        }}
                                    />
                                    <CheckboxControlled
                                        name={formMeta.fields.privacyPolicy.name}
                                        control={formProviderMethods.control}
                                        formName={formMeta.formName}
                                        render={(checkbox) => (
                                            <div className="mb-7">
                                                <ChoiceFormLine>{checkbox}</ChoiceFormLine>
                                            </div>
                                        )}
                                        checkboxProps={{
                                            label: formMeta.fields.privacyPolicy.label,
                                        }}
                                    />
                                    <Button
                                        dataTestId={TEST_IDENTIFIER}
                                        type="submit"
                                        variant="primary"
                                        isRounder
                                        style={{ width: '100%' }}
                                        isWithDisabledLook={!formProviderMethods.formState.isValid}
                                    >
                                        {t('Create account')}
                                    </Button>
                                </FormProvider>
                            </Form>
                        </div>
                    </div>
                </div>
            </Webline>
            {isErrorPopupVisible && (
                <ErrorPopup
                    onCloseCallback={() => setErrorPopupVisibility(false)}
                    fields={formMeta.fields}
                    gtmMessageOrigin={GtmMessageOriginType.order_confirmation_page}
                />
            )}
        </>
    );
};
