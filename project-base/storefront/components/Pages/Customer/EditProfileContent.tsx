import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { Form, FormBlockWrapper, FormButtonWrapper, FormHeading, FormContentWrapper } from 'components/Forms/Form/Form';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { Select } from 'components/Forms/Select/Select';
import { PasswordInputControlled } from 'components/Forms/TextInput/PasswordInputControlled';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { AddressList } from 'components/Pages/Customer/AddressList';
import {
    useCustomerChangeProfileForm,
    useCustomerChangeProfileFormMeta,
} from 'components/Pages/Customer/customerChangeProfileFormMeta';
import { useChangePasswordMutation } from 'graphql/requests/customer/mutations/ChangePasswordMutation.generated';
import { useChangePersonalDataMutation } from 'graphql/requests/customer/mutations/ChangePersonalDataMutation.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import useTranslation from 'next-translate/useTranslation';
import { Controller, FormProvider, Path, SubmitHandler, UseFormReturn } from 'react-hook-form';
import { CurrentCustomerType } from 'types/customer';
import { CustomerChangeProfileFormType } from 'types/form';
import { CombinedError } from 'urql';
import { useCountriesAsSelectOptions } from 'utils/countries/useCountriesAsSelectOptions';
import { getUserFriendlyErrors } from 'utils/errors/friendlyErrorMessageParser';
import { useErrorPopup } from 'utils/forms/useErrorPopup';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

type EditProfileContentProps = {
    currentCustomerUser: CurrentCustomerType;
};

export const EditProfileContent: FC<EditProfileContentProps> = ({ currentCustomerUser }) => {
    const { t } = useTranslation();
    const [, customerEditProfile] = useChangePersonalDataMutation();

    const [formProviderMethods] = useCustomerChangeProfileForm({
        ...currentCustomerUser,
        country: {
            label: currentCustomerUser.country.name,
            value: currentCustomerUser.country.code,
        },
    });
    const formMeta = useCustomerChangeProfileFormMeta(formProviderMethods);
    const countriesAsSelectOptions = useCountriesAsSelectOptions();
    const [, changePassword] = useChangePasswordMutation();
    const hasDeliveryAddresses = currentCustomerUser.deliveryAddresses.length > 0;

    useErrorPopup(formProviderMethods, formMeta.fields, undefined, GtmMessageOriginType.other);

    const onSubmitCustomerChangeProfileFormHandler: SubmitHandler<CustomerChangeProfileFormType> = async (
        customerChangeProfileFormData,
        event,
    ) => {
        event?.preventDefault();

        onChangeProfileHandler(customerChangeProfileFormData);
        onChangePasswordHandler(customerChangeProfileFormData);
    };

    const onChangeProfileHandler = async (customerChangeProfileFormData: CustomerChangeProfileFormType) => {
        const changeProfileResult = await customerEditProfile({
            input: {
                firstName: customerChangeProfileFormData.firstName,
                lastName: customerChangeProfileFormData.lastName,
                telephone: customerChangeProfileFormData.telephone,
                street: customerChangeProfileFormData.street,
                city: customerChangeProfileFormData.city,
                country: customerChangeProfileFormData.country.value,
                postcode: customerChangeProfileFormData.postcode,
                companyCustomer: customerChangeProfileFormData.companyCustomer,
                companyName: customerChangeProfileFormData.companyName,
                companyNumber: customerChangeProfileFormData.companyNumber,
                companyTaxNumber: customerChangeProfileFormData.companyTaxNumber,
                newsletterSubscription: customerChangeProfileFormData.newsletterSubscription,
            },
        });

        handleUpdateResult(
            changeProfileResult.data?.ChangePersonalData !== undefined,
            changeProfileResult.error,
            formProviderMethods,
            formMeta.messages,
        );
    };

    const onChangePasswordHandler = async (customerChangeProfileFormData: CustomerChangeProfileFormType) => {
        if (
            customerChangeProfileFormData.newPassword === '' ||
            customerChangeProfileFormData.newPasswordConfirm === ''
        ) {
            return;
        }

        const changePasswordResult = await changePassword({
            email: customerChangeProfileFormData.email,
            oldPassword: customerChangeProfileFormData.oldPassword,
            newPassword: customerChangeProfileFormData.newPassword,
        });

        handleUpdateResult(
            changePasswordResult.data?.ChangePassword !== undefined,
            changePasswordResult.error,
            formProviderMethods,
            {
                success: t('Your password has been changed.'),
                error: t('There was an error while changing your password'),
            },
        );
    };

    const handleUpdateResult = (
        isResultOk: boolean,
        error: CombinedError | undefined,
        formProviderMethods: UseFormReturn<CustomerChangeProfileFormType>,
        messages: { success?: string; error?: string },
        callbacks?: { success?: () => void; error?: () => void },
    ) => {
        if (isResultOk) {
            if (messages.success !== undefined) {
                showSuccessMessage(messages.success);
                formProviderMethods.setValue('oldPassword', '', { shouldValidate: true });
                formProviderMethods.setValue('newPassword', '', { shouldValidate: true });
                formProviderMethods.setValue('newPasswordConfirm', '', { shouldValidate: true });
            }
            if (callbacks?.success !== undefined) {
                callbacks.success();
            }
        }

        if (error === undefined) {
            return;
        }

        const { userError, applicationError } = getUserFriendlyErrors(error, t);

        if (applicationError !== undefined) {
            if (messages.error !== undefined) {
                showErrorMessage(messages.error, GtmMessageOriginType.other);
            }
            if (callbacks?.error !== undefined) {
                callbacks.error();
            }
        }

        if (userError?.validation !== undefined) {
            for (const fieldName in userError.validation) {
                formProviderMethods.setError(
                    fieldName as Path<CustomerChangeProfileFormType>,
                    userError.validation[fieldName],
                );
            }
        }
    };

    return (
        <FormProvider {...formProviderMethods}>
            <Form onSubmit={formProviderMethods.handleSubmit(onSubmitCustomerChangeProfileFormHandler)}>
                <FormContentWrapper>
                    <FormBlockWrapper>
                        <FormHeading>{t('Personal data')}</FormHeading>
                        <TextInputControlled
                            control={formProviderMethods.control}
                            formName={formMeta.formName}
                            name={formMeta.fields.email.name}
                            render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                            textInputProps={{
                                label: formMeta.fields.email.label,
                                required: false,
                                disabled: true,
                                type: 'email',
                                autoComplete: 'email',
                            }}
                        />
                        <FormLine bottomGap>
                            <div>
                                {t(
                                    'To prevent the possibility of account theft, it is necessary to deal with the change of e-mail individually. If your e-mail address has changed, please contact us.',
                                )}
                            </div>
                        </FormLine>
                        <FormColumn>
                            <TextInputControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.firstName.name}
                                render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                                textInputProps={{
                                    label: formMeta.fields.firstName.label,
                                    required: true,
                                    type: 'text',
                                    autoComplete: 'given-name',
                                }}
                            />
                            <TextInputControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.lastName.name}
                                render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                                textInputProps={{
                                    label: formMeta.fields.lastName.label,
                                    required: true,
                                    type: 'text',
                                    autoComplete: 'family-name',
                                }}
                            />
                        </FormColumn>
                        <TextInputControlled
                            control={formProviderMethods.control}
                            formName={formMeta.formName}
                            name={formMeta.fields.telephone.name}
                            render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                            textInputProps={{
                                label: formMeta.fields.telephone.label,
                                required: true,
                                type: 'tel',
                                autoComplete: 'tel',
                            }}
                        />
                        <CheckboxControlled
                            control={formProviderMethods.control}
                            formName={formMeta.formName}
                            name={formMeta.fields.newsletterSubscription.name}
                            render={(checkbox) => <FormLine>{checkbox}</FormLine>}
                            checkboxProps={{
                                label: formMeta.fields.newsletterSubscription.label,
                            }}
                        />
                    </FormBlockWrapper>

                    <FormBlockWrapper>
                        <FormHeading>{t('Change password')}</FormHeading>
                        <PasswordInputControlled
                            control={formProviderMethods.control}
                            formName={formMeta.formName}
                            name={formMeta.fields.oldPassword.name}
                            passwordInputProps={{
                                label: formMeta.fields.oldPassword.label,
                            }}
                            render={(passwordInput) => (
                                <FormColumn>
                                    <FormLine bottomGap>{passwordInput}</FormLine>
                                </FormColumn>
                            )}
                        />
                        <FormColumn>
                            <PasswordInputControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.newPassword.name}
                                render={(passwordInput) => <FormLine bottomGap>{passwordInput}</FormLine>}
                                passwordInputProps={{
                                    label: formMeta.fields.newPassword.label,
                                }}
                            />
                            <PasswordInputControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.newPasswordConfirm.name}
                                render={(passwordInput) => <FormLine bottomGap>{passwordInput}</FormLine>}
                                passwordInputProps={{
                                    label: formMeta.fields.newPasswordConfirm.label,
                                }}
                            />
                        </FormColumn>
                    </FormBlockWrapper>

                    {currentCustomerUser.companyCustomer && (
                        <FormBlockWrapper>
                            <>
                                <FormHeading>{t('Company information')}</FormHeading>
                                <TextInputControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.companyName.name}
                                    render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                                    textInputProps={{
                                        label: formMeta.fields.companyName.label,
                                        required: false,
                                        type: 'text',
                                        autoComplete: 'organization',
                                    }}
                                />
                                <TextInputControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.companyNumber.name}
                                    render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                                    textInputProps={{
                                        label: formMeta.fields.companyNumber.label,
                                        required: false,
                                        type: 'text',
                                    }}
                                />
                                <TextInputControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.companyTaxNumber.name}
                                    render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                                    textInputProps={{
                                        label: formMeta.fields.companyTaxNumber.label,
                                        required: false,
                                        type: 'text',
                                    }}
                                />
                            </>
                        </FormBlockWrapper>
                    )}

                    <FormBlockWrapper className={hasDeliveryAddresses ? '' : 'border-b-0 !pb-0'}>
                        <FormHeading>{t('Billing address')}</FormHeading>
                        <TextInputControlled
                            control={formProviderMethods.control}
                            formName={formMeta.formName}
                            name={formMeta.fields.street.name}
                            render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                            textInputProps={{
                                label: formMeta.fields.street.label,
                                required: true,
                                type: 'text',
                                autoComplete: 'street-address',
                            }}
                        />
                        <FormColumn>
                            <TextInputControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.city.name}
                                render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                                textInputProps={{
                                    label: formMeta.fields.city.label,
                                    required: true,
                                    type: 'text',
                                    autoComplete: 'address-level2',
                                }}
                            />
                            <TextInputControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.postcode.name}
                                render={(textInput) => (
                                    <FormLine bottomGap isSmallInput>
                                        {textInput}
                                    </FormLine>
                                )}
                                textInputProps={{
                                    label: formMeta.fields.postcode.label,
                                    required: true,
                                    type: 'text',
                                    autoComplete: 'postal-code',
                                }}
                            />
                        </FormColumn>
                        <FormLine bottomGap>
                            <Controller
                                name={formMeta.fields.country.name}
                                render={({ fieldState: { invalid, error }, field }) => (
                                    <>
                                        <Select
                                            hasError={invalid}
                                            label={formMeta.fields.country.label}
                                            options={countriesAsSelectOptions}
                                            value={countriesAsSelectOptions.find(
                                                (option) => option.value === field.value.value,
                                            )}
                                            onChange={field.onChange}
                                        />
                                        <FormLineError error={error} inputType="select" />
                                    </>
                                )}
                            />
                        </FormLine>
                    </FormBlockWrapper>

                    <FormBlockWrapper className={hasDeliveryAddresses ? '' : '!pt-0'}>
                        {hasDeliveryAddresses && (
                            <>
                                <FormHeading>{t('Delivery addresses')}</FormHeading>
                                <FormLine>
                                    <AddressList
                                        defaultDeliveryAddress={currentCustomerUser.defaultDeliveryAddress}
                                        deliveryAddresses={currentCustomerUser.deliveryAddresses}
                                    />
                                </FormLine>
                            </>
                        )}
                        <FormButtonWrapper>
                            <SubmitButton className="mx-auto">{t('Save profile')}</SubmitButton>
                        </FormButtonWrapper>
                    </FormBlockWrapper>
                </FormContentWrapper>
            </Form>
        </FormProvider>
    );
};
