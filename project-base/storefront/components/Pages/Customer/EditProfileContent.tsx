import { Button } from 'components/Forms/Button/Button';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper, FormHeading } from 'components/Forms/Form/Form';
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
import { usePasswordRecoveryMutation } from 'graphql/requests/passwordRecovery/mutations/PasswordRecoveryMutation.generated';
import { GtmFormType } from 'gtm/enums/GtmFormType';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { onGtmSendFormEventHandler } from 'gtm/handlers/onGtmSendFormEventHandler';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { Controller, FormProvider, SubmitHandler } from 'react-hook-form';
import { useSessionStore } from 'store/useSessionStore';
import { CurrentCustomerType } from 'types/customer';
import { CustomerChangeProfileFormType } from 'types/form';
import { useCurrentCustomerUserPermissions } from 'utils/auth/useCurrentCustomerUserPermissions';
import { useCountriesAsSelectOptions } from 'utils/countries/useCountriesAsSelectOptions';
import { handleFormErrors } from 'utils/forms/handleFormErrors';
import { useErrorPopup } from 'utils/forms/useErrorPopup';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

const DeliveryAddressPopup = dynamic(
    () => import('components/Blocks/Popup/DeliveryAddressPopup').then((component) => component.DeliveryAddressPopup),
    {
        ssr: false,
    },
);

type EditProfileContentProps = {
    currentCustomerUser: CurrentCustomerType;
};

export const EditProfileContent: FC<EditProfileContentProps> = ({ currentCustomerUser }) => {
    const { t } = useTranslation();
    const [, customerEditProfile] = useChangePersonalDataMutation();
    const [, resetPassword] = usePasswordRecoveryMutation();
    const [, changePassword] = useChangePasswordMutation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    const [formProviderMethods] = useCustomerChangeProfileForm({
        ...currentCustomerUser,
        country: {
            label: currentCustomerUser.country.name,
            value: currentCustomerUser.country.code,
        },
    });
    const formMeta = useCustomerChangeProfileFormMeta(formProviderMethods);
    const countriesAsSelectOptions = useCountriesAsSelectOptions();
    const { canManageProfile } = useCurrentCustomerUserPermissions();

    useErrorPopup(formProviderMethods, formMeta.fields, undefined, GtmMessageOriginType.other);

    const onSubmitCustomerChangeProfileFormHandler: SubmitHandler<CustomerChangeProfileFormType> = async (
        customerChangeProfileFormData,
        event,
    ) => {
        event?.preventDefault();

        onChangeProfileHandler(customerChangeProfileFormData);
        onChangePasswordHandler(customerChangeProfileFormData);
    };

    const onResetPasswordHandler = async () => {
        const resetPasswordResult = await resetPassword({ email: currentCustomerUser.email });

        if (resetPasswordResult.data?.RequestPasswordRecovery !== undefined) {
            showSuccessMessage(t('We sent an email with further steps to your address'));
            onGtmSendFormEventHandler(GtmFormType.forgotten_password);
        }
    };

    const onChangeProfileHandler = async (customerChangeProfileFormData: CustomerChangeProfileFormType) => {
        const changeProfileResult = await customerEditProfile({
            input: {
                billingAddressUuid: currentCustomerUser.billingAddressUuid,
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

        if (changeProfileResult.data?.ChangePersonalData !== undefined) {
            showSuccessMessage(formMeta.messages.success);
        }

        handleFormErrors(changeProfileResult.error, formProviderMethods, t, formMeta.messages.error);
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

        if (changePasswordResult.data?.ChangePassword !== undefined) {
            showSuccessMessage(t('Your password has been changed.'));
        }

        handleFormErrors(
            changePasswordResult.error,
            formProviderMethods,
            t,
            t('There was an error while changing your password'),
        );
    };

    const openDeliveryAddressPopup = (e: React.MouseEvent<HTMLButtonElement, MouseEvent>) => {
        e.stopPropagation();
        updatePortalContent(<DeliveryAddressPopup />);
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
                        {currentCustomerUser.hasPasswordSet ? (
                            <>
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
                            </>
                        ) : (
                            <Button size="small" onClick={onResetPasswordHandler}>
                                {t('Send me a link to set a new password')}
                            </Button>
                        )}
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
                                        required: true,
                                        type: 'text',
                                        autoComplete: 'organization',
                                        disabled: !canManageProfile,
                                    }}
                                />
                                <TextInputControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.companyNumber.name}
                                    render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                                    textInputProps={{
                                        label: formMeta.fields.companyNumber.label,
                                        required: true,
                                        type: 'text',
                                        disabled: !canManageProfile,
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
                                        disabled: !canManageProfile,
                                    }}
                                />
                            </>
                        </FormBlockWrapper>
                    )}

                    <FormBlockWrapper>
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
                                disabled: !canManageProfile,
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
                                    disabled: !canManageProfile,
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
                                    disabled: !canManageProfile,
                                }}
                            />
                        </FormColumn>
                        <FormLine bottomGap>
                            <Controller
                                name={formMeta.fields.country.name}
                                render={({ fieldState: { invalid, error }, field }) => (
                                    <>
                                        <Select
                                            required
                                            hasError={invalid}
                                            isDisabled={!canManageProfile}
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

                    <FormBlockWrapper>
                        <FormHeading className="flex justify-between">
                            {t('Delivery addresses')}
                            <Button size="small" variant="inverted" onClick={(e) => openDeliveryAddressPopup(e)}>
                                {t('Add new address')}
                            </Button>
                        </FormHeading>
                        <FormLine>
                            <AddressList
                                defaultDeliveryAddress={currentCustomerUser.defaultDeliveryAddress}
                                deliveryAddresses={currentCustomerUser.deliveryAddresses}
                            />
                        </FormLine>
                        <FormButtonWrapper>
                            <SubmitButton className="mx-auto">{t('Save profile')}</SubmitButton>
                        </FormButtonWrapper>
                    </FormBlockWrapper>
                </FormContentWrapper>
            </Form>
        </FormProvider>
    );
};
