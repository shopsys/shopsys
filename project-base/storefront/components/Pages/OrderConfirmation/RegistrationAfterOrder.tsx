import { useRegistrationAfterOrderForm, useRegistrationAfterOrderFormMeta } from './registrationAfterOrderFormMeta';
import { CheckmarkBadgeIcon } from 'components/Basic/Icon/CheckmarkBadgeIcon';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { Form, FormBlockWrapper, FormContentWrapper } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { PasswordInputControlled } from 'components/Forms/TextInput/PasswordInputControlled';
import { TIDs } from 'cypress/tids';
import { useIsCustomerUserRegisteredQuery } from 'graphql/requests/customer/queries/IsCustomerUserRegisteredQuery.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import Trans from 'next-translate/Trans';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { OrderConfirmationUrlQuery } from 'pages/order-confirmation';
import { useRef } from 'react';
import { FormProvider } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { RegistrationAfterOrderFormType } from 'types/form';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { useRegistration } from 'utils/auth/useRegistration';
import { getUserFriendlyErrors } from 'utils/errors/friendlyErrorMessageParser';
import { blurInput } from 'utils/forms/blurInput';
import { useErrorPopup } from 'utils/forms/useErrorPopup';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';

export const RegistrationAfterOrder: FC = () => {
    const { t } = useTranslation();
    const [formProviderMethods] = useRegistrationAfterOrderForm();
    const formMeta = useRegistrationAfterOrderFormMeta(formProviderMethods);
    const register = useRegistration();
    const isInvalidRegistrationRef = useRef(false);
    const { query } = useRouter();
    const { orderUuid, orderEmail, registrationData } = query as OrderConfirmationUrlQuery;
    const isUserLoggedIn = useIsUserLoggedIn();
    const parsedRegistrationData = useRef<ContactInformation | undefined>(
        registrationData ? (JSON.parse(registrationData) as ContactInformation) : undefined,
    );

    useErrorPopup(formProviderMethods, formMeta.fields, undefined, GtmMessageOriginType.order_confirmation_page);

    const [{ data: isCustomerUserRegisteredData, fetching: isInformationAboutUserRegistrationFetching }] =
        useIsCustomerUserRegisteredQuery({
            variables: {
                email: orderEmail!,
            },
            pause: !orderEmail,
        });

    const onRegistrationHandler = async (registrationAfterOrderFormData: RegistrationAfterOrderFormType) => {
        if (!parsedRegistrationData.current || !orderUuid) {
            return;
        }

        blurInput();
        const registrationError = await register({
            ...registrationAfterOrderFormData,
            ...parsedRegistrationData.current,
            country: parsedRegistrationData.current.country.value,
            companyCustomer: parsedRegistrationData.current.customer === 'companyCustomer',
            cartUuid: null,
            lastOrderUuid: orderUuid,
            billingAddressUuid: null,
        });

        if (registrationError) {
            const validationErrors = getUserFriendlyErrors(registrationError, t).userError?.validation;
            for (const fieldName in validationErrors) {
                if (fieldName === 'password' || fieldName === 'input') {
                    showErrorMessage(validationErrors[fieldName].message, GtmMessageOriginType.order_confirmation_page);
                } else {
                    isInvalidRegistrationRef.current = true;
                    showErrorMessage(t('There was an error with your registration. Please try again later.'));
                    break;
                }
            }
        }
    };

    if (
        !parsedRegistrationData.current ||
        isUserLoggedIn ||
        !orderUuid ||
        isInformationAboutUserRegistrationFetching ||
        isCustomerUserRegisteredData?.isCustomerUserRegistered === true
    ) {
        return null;
    }

    return (
        <div className="relative mb-20 flex flex-col rounded border-2 border-borderAccent lg:flex-row">
            <div className="w-full p-5 lg:w-1/2 lg:px-10 lg:py-8">
                <div className="mb-5 text-4xl font-bold leading-10 [&>strong]:text-textAccent">
                    <Trans
                        components={{ 0: <br />, 1: <strong /> }}
                        i18nKey="Finish registration to loyalty program."
                    />
                </div>

                <ul>
                    {[
                        t('You will have an overview of your orders and complaints'),
                        t('Collecting points with every order'),
                        t('Possibility of purchases for better prices'),
                        t('Exclusive products as a part of the loyalty program'),
                    ].map((text) => (
                        <li key={text} className="relative mb-3 flex gap-2">
                            <CheckmarkBadgeIcon className="min-w-4 text-textSuccess" />
                            <span>{text}</span>
                        </li>
                    ))}
                </ul>
            </div>

            <div className="flex w-full flex-col items-center justify-center p-5 lg:w-1/2 lg:px-10 lg:py-8">
                <div className="w-full lg:max-w-sm">
                    <FormProvider {...formProviderMethods}>
                        <Form
                            className="flex flex-col gap-5"
                            onSubmit={formProviderMethods.handleSubmit(onRegistrationHandler)}
                        >
                            <FormContentWrapper className="vl:px-5">
                                <FormBlockWrapper>
                                    <PasswordInputControlled
                                        control={formProviderMethods.control}
                                        formName={formMeta.formName}
                                        name={formMeta.fields.password.name}
                                        passwordInputProps={{
                                            label: formMeta.fields.password.label,
                                        }}
                                        render={(passwordInput) => (
                                            <div className="mb-7">
                                                <FormLine>{passwordInput}</FormLine>
                                            </div>
                                        )}
                                    />
                                    <CheckboxControlled
                                        control={formProviderMethods.control}
                                        formName={formMeta.formName}
                                        name={formMeta.fields.privacyPolicy.name}
                                        render={(checkbox) => <FormLine>{checkbox}</FormLine>}
                                        checkboxProps={{
                                            label: formMeta.fields.privacyPolicy.label,
                                        }}
                                    />
                                </FormBlockWrapper>
                            </FormContentWrapper>
                            <SubmitButton
                                className="w-full"
                                isDisabled={isInvalidRegistrationRef.current}
                                isWithDisabledLook={!formProviderMethods.formState.isValid}
                                tid={TIDs.registration_after_order_submit_button}
                            >
                                {t('Create account')}
                            </SubmitButton>
                        </Form>
                    </FormProvider>
                </div>
            </div>
        </div>
    );
};
