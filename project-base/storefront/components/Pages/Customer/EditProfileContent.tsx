import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { Form } from 'components/Forms/Form/Form';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine';
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
import dynamic from 'next/dynamic';
import { Controller, FormProvider, Path, SubmitHandler, UseFormReturn } from 'react-hook-form';
import { CurrentCustomerType } from 'types/customer';
import { CustomerChangeProfileFormType } from 'types/form';
import { CombinedError } from 'urql';
import { useCountriesAsSelectOptions } from 'utils/countries/useCountriesAsSelectOptions';
import { getUserFriendlyErrors } from 'utils/errors/friendlyErrorMessageParser';
import { useErrorPopupVisibility } from 'utils/forms/useErrorPopupVisibility';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

const ErrorPopup = dynamic(() => import('components/Forms/Lib/ErrorPopup').then((component) => component.ErrorPopup));

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
    const [isErrorPopupVisible, setErrorPopupVisibility] = useErrorPopupVisibility(formProviderMethods);
    const countriesAsSelectOptions = useCountriesAsSelectOptions();
    const [, changePassword] = useChangePasswordMutation();

    const onSubmitCustomerChangeProfileFormHandler: SubmitHandler<CustomerChangeProfileFormType> = async (
        data,
        event,
    ) => {
        event?.preventDefault();

        onChangeProfileHandler(data);
        onChangePasswordHandler(data);
    };

    const onChangeProfileHandler = async (data: CustomerChangeProfileFormType) => {
        const changeProfileResult = await customerEditProfile({
            input: {
                firstName: data.firstName,
                lastName: data.lastName,
                telephone: data.telephone,
                street: data.street,
                city: data.city,
                country: data.country.value,
                postcode: data.postcode,
                companyCustomer: data.companyCustomer,
                companyName: data.companyName,
                companyNumber: data.companyNumber,
                companyTaxNumber: data.companyTaxNumber,
                newsletterSubscription: data.newsletterSubscription,
            },
        });

        handleUpdateResult(
            changeProfileResult.data?.ChangePersonalData !== undefined,
            changeProfileResult.error,
            formProviderMethods,
            formMeta.messages,
        );
    };

    const onChangePasswordHandler = async (data: CustomerChangeProfileFormType) => {
        if (data.passwordFirst === '' || data.passwordSecond === '') {
            return;
        }

        const changePasswordResult = await changePassword({
            email: data.email,
            oldPassword: data.passwordOld,
            newPassword: data.passwordFirst,
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
                formProviderMethods.setValue('passwordOld', '', { shouldValidate: true });
                formProviderMethods.setValue('passwordFirst', '', { shouldValidate: true });
                formProviderMethods.setValue('passwordSecond', '', { shouldValidate: true });
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
        <>
            <FormProvider {...formProviderMethods}>
                <Form onSubmit={formProviderMethods.handleSubmit(onSubmitCustomerChangeProfileFormHandler)}>
                    <div className="h2 mb-3">{t('Personal data')}</div>
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
                            render={(textInput) => (
                                <FormLine bottomGap className="w-full flex-none lg:w-1/2">
                                    {textInput}
                                </FormLine>
                            )}
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
                            render={(textInput) => (
                                <FormLine bottomGap className="w-full flex-none lg:w-1/2">
                                    {textInput}
                                </FormLine>
                            )}
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
                        checkboxProps={{
                            label: formMeta.fields.newsletterSubscription.label,
                        }}
                        render={(checkbox) => (
                            <FormLine bottomGap>
                                <ChoiceFormLine>{checkbox}</ChoiceFormLine>
                            </FormLine>
                        )}
                    />
                    <div className="h2 mb-3">{t('Change password')}</div>
                    <PasswordInputControlled
                        control={formProviderMethods.control}
                        formName={formMeta.formName}
                        name={formMeta.fields.passwordOld.name}
                        passwordInputProps={{
                            label: formMeta.fields.passwordOld.label,
                        }}
                        render={(passwordInput) => (
                            <FormColumn>
                                <FormLine bottomGap className="w-full flex-none lg:w-1/2">
                                    {passwordInput}
                                </FormLine>
                            </FormColumn>
                        )}
                    />
                    <FormColumn>
                        <PasswordInputControlled
                            control={formProviderMethods.control}
                            formName={formMeta.formName}
                            name={formMeta.fields.passwordFirst.name}
                            passwordInputProps={{
                                label: formMeta.fields.passwordFirst.label,
                            }}
                            render={(passwordInput) => (
                                <FormLine bottomGap className="w-full flex-none lg:w-1/2">
                                    {passwordInput}
                                </FormLine>
                            )}
                        />
                        <PasswordInputControlled
                            control={formProviderMethods.control}
                            formName={formMeta.formName}
                            name={formMeta.fields.passwordSecond.name}
                            passwordInputProps={{
                                label: formMeta.fields.passwordSecond.label,
                            }}
                            render={(passwordInput) => (
                                <FormLine bottomGap className="w-full flex-none lg:w-1/2">
                                    {passwordInput}
                                </FormLine>
                            )}
                        />
                    </FormColumn>
                    {currentCustomerUser.companyCustomer && (
                        <>
                            <div className="h2 mb-3">{t('Company information')}</div>
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
                    )}
                    <div className="h2 mb-3">{t('Billing address')}</div>
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
                                <FormLine bottomGap className="w-full flex-none lg:w-[142px]">
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
                    {currentCustomerUser.deliveryAddresses.length > 0 && (
                        <>
                            <div className="h2 mb-3">{t('Delivery addresses')}</div>
                            <FormLine bottomGap>
                                <AddressList
                                    defaultDeliveryAddress={currentCustomerUser.defaultDeliveryAddress}
                                    deliveryAddresses={currentCustomerUser.deliveryAddresses}
                                />
                            </FormLine>
                        </>
                    )}
                    <SubmitButton>{t('Save profile')}</SubmitButton>
                </Form>
            </FormProvider>
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
