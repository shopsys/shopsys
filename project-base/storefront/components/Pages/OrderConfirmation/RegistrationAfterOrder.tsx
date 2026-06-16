import { ThumbUp } from 'components/Basic/Icon/ThumbUp';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { Form } from 'components/Forms/Form/Form';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { PasswordInputControlled } from 'components/Forms/TextInput/PasswordInputControlled';
import { TIDs } from 'cypress/tids';
import { useCouldBeCustomerRegisteredQuery } from 'graphql/requests/customer/queries/CouldBeCustomerRegisteredQuery.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { useState } from 'react';
import { FormProvider } from 'react-hook-form';
import { RegistrationAfterOrderFormType } from 'types/form';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { useRegistration } from 'utils/auth/useRegistration';
import { getUserFriendlyErrors } from 'utils/errors/friendlyErrorMessageParser';
import { blurInput } from 'utils/forms/blurInput';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { useRegistrationAfterOrderForm, useRegistrationAfterOrderFormMeta } from './registrationAfterOrderFormMeta';

type RegistrationAfterOrderProps = {
    orderUuid: string;
    companyNumber?: string | null;
    orderEmail: string;
    orderUrlHash: string;
};

export const RegistrationAfterOrder: FC<RegistrationAfterOrderProps> = ({
    orderUuid,
    companyNumber,
    orderEmail,
    orderUrlHash,
}) => {
    const { t } = useTranslation();
    const [formProviderMethods] = useRegistrationAfterOrderForm();
    const formMeta = useRegistrationAfterOrderFormMeta();
    const { registerByOrder } = useRegistration();
    const [isInvalidRegistration, setIsInvalidRegistration] = useState(false);
    const isUserLoggedIn = useIsUserLoggedIn();

    const [{ data: couldBeCustomerRegisteredData, fetching: isInformationAboutUserRegistrationFetching }] =
        useCouldBeCustomerRegisteredQuery({
            variables: {
                email: orderEmail,
                companyNumber: companyNumber,
            },
            pause: !orderEmail,
        });

    const onRegistrationHandler = async (registrationAfterOrderFormData: RegistrationAfterOrderFormType) => {
        if (!orderUrlHash || !orderUuid) {
            return;
        }

        blurInput();
        const registrationError = await registerByOrder({
            ...registrationAfterOrderFormData,
            orderUrlHash,
        });

        if (registrationError) {
            const validationErrors = getUserFriendlyErrors(registrationError, t).userError?.validation;
            for (const fieldName in validationErrors) {
                const fieldError = validationErrors[fieldName];
                if (fieldName === 'password' || fieldName === 'input') {
                    const errorMessage = fieldError?.message;
                    if (errorMessage) {
                        showErrorMessage(errorMessage, GtmMessageOriginType.order_confirmation_page);
                    }
                } else {
                    setIsInvalidRegistration(true);
                    showErrorMessage(t('There was an error with your registration. Please try again later.'));
                    break;
                }
            }
        }
    };

    if (
        !orderUrlHash ||
        isUserLoggedIn ||
        !orderUuid ||
        isInformationAboutUserRegistrationFetching ||
        couldBeCustomerRegisteredData?.couldBeCustomerRegisteredQuery === false
    ) {
        return null;
    }

    const registrationAfterOrderUsp = [
        t('You will have an overview of your orders and complaints'),
        t('Collecting points with every order'),
        t('Possibility of purchases for better prices'),
        t('Exclusive products as a part of the loyalty program'),
    ];

    return (
        <div className="flex flex-col rounded-xl bg-background-more p-5">
            <h2>{t('Finish registration to loyalty program.')}</h2>

            <p className="sr-only" id="registration-after-order-password-label">
                {t('Finish registration to loyalty program by entering your password')}
            </p>

            <ul className="flex flex-col gap-2 py-5">
                {registrationAfterOrderUsp.map((text) => (
                    <li key={text} className="flex items-center gap-2">
                        <ThumbUp className="size-6 text-text-accent" />
                        <span className="h5 text-text-accent">{text}</span>
                    </li>
                ))}
            </ul>

            <FormProvider {...formProviderMethods}>
                <Form
                    className="flex flex-col gap-4"
                    formName={formMeta.formName}
                    onSubmit={formProviderMethods.handleSubmit(onRegistrationHandler)}
                >
                    <fieldset>
                        <legend className="h4 mb-4">{t('Choose a password')}</legend>

                        <FormColumn>
                            <PasswordInputControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.password.name}
                                width="half"
                                passwordInputProps={{
                                    label: formMeta.fields.password.label,
                                    autoComplete: 'new-password',
                                    'aria-labelledby': 'registration-after-order-password-label',
                                }}
                            />

                            <PasswordInputControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.passwordConfirm.name}
                                width="half"
                                passwordInputProps={{
                                    label: formMeta.fields.passwordConfirm.label,
                                    autoComplete: 'new-password-confirm',
                                }}
                            />
                        </FormColumn>
                    </fieldset>

                    <fieldset>
                        <legend className="sr-only">{t('Privacy policy')}</legend>

                        <CheckboxControlled
                            control={formProviderMethods.control}
                            formName={formMeta.formName}
                            name={formMeta.fields.privacyPolicy.name}
                            render={(checkbox) => <FormLine>{checkbox}</FormLine>}
                            checkboxProps={{
                                label: formMeta.fields.privacyPolicy.label,
                                required: true,
                            }}
                        />
                    </fieldset>

                    <SubmitButton
                        aria-label={t('Submit form to create your new account', { ns: 'accessibility' })}
                        className="self-start"
                        hasDisabledCursor={!formProviderMethods.formState.isValid}
                        hasDisabledLook={isInvalidRegistration}
                        size="large"
                        tid={TIDs.registration_after_order_submit_button}
                    >
                        {t('Create account')}
                    </SubmitButton>
                </Form>
            </FormProvider>
        </div>
    );
};
