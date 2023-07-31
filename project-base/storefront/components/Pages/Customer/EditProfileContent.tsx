import { Heading } from 'components/Basic/Heading/Heading';
import { Button } from 'components/Forms/Button/Button';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { Form } from 'components/Forms/Form/Form';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { Select } from 'components/Forms/Select/Select';
import { PasswordInputControlled } from 'components/Forms/TextInput/PasswordInputControlled';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { showErrorMessage, showSuccessMessage } from 'components/Helpers/toasts';
import { AddressList } from 'components/Pages/Customer/AddressList';
import { useCustomerChangeProfileForm, useCustomerChangeProfileFormMeta } from 'components/Pages/Customer/formMeta';
import {
    useChangePasswordMutationApi,
    useChangePersonalDataMutationApi,
    useCountriesQueryApi,
} from 'graphql/generated';
import { getUserFriendlyErrors } from 'helpers/errors/friendlyErrorMessageParser';
import { mapCountriesToSelectOptions } from 'helpers/mappers/country';
import { useErrorPopupVisibility } from 'hooks/forms/useErrorPopupVisibility';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import dynamic from 'next/dynamic';
import { useMemo } from 'react';
import { Controller, FormProvider, Path, SubmitHandler, UseFormReturn } from 'react-hook-form';
import { CurrentCustomerType } from 'types/customer';
import { CustomerChangeProfileFormType } from 'types/form';
import { GtmMessageOriginType } from 'types/gtm/enums';
import { CombinedError } from 'urql';

const ErrorPopup = dynamic(() => import('components/Forms/Lib/ErrorPopup').then((component) => component.ErrorPopup));

type EditProfileContentProps = {
    currentCustomerUser: CurrentCustomerType;
};

const TEST_IDENTIFIER = 'form-edit-profile';

export const EditProfileContent: FC<EditProfileContentProps> = ({ currentCustomerUser }) => {
    const t = useTypedTranslationFunction();
    const [, customerEditProfile] = useChangePersonalDataMutationApi();

    const [formProviderMethods] = useCustomerChangeProfileForm({
        ...currentCustomerUser,
        country: {
            label: currentCustomerUser.country.name,
            value: currentCustomerUser.country.code,
        },
    });
    const formMeta = useCustomerChangeProfileFormMeta(formProviderMethods);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useErrorPopupVisibility(formProviderMethods);
    const [{ data: countriesData }] = useCountriesQueryApi();
    const countriesAsSelectOptions = useMemo(
        () => mapCountriesToSelectOptions(countriesData?.countries),
        [countriesData?.countries],
    );
    const [, changePassword] = useChangePasswordMutationApi();

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
                formProviderMethods.setValue('passwordOld', '');
                formProviderMethods.setValue('passwordFirst', '');
                formProviderMethods.setValue('passwordSecond', '');
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
                    <Heading type="h2">{t('Personal data')}</Heading>
                    <TextInputControlled
                        control={formProviderMethods.control}
                        name={formMeta.fields.email.name}
                        render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                        formName={formMeta.formName}
                        textInputProps={{
                            label: formMeta.fields.email.label,
                            required: false,
                            disabled: true,
                            type: 'email',
                            autoComplete: 'email',
                            dataTestId: TEST_IDENTIFIER + '-' + formMeta.fields.email.name,
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
                            name={formMeta.fields.firstName.name}
                            render={(textInput) => (
                                <FormLine bottomGap className="w-full flex-none lg:w-1/2">
                                    {textInput}
                                </FormLine>
                            )}
                            formName={formMeta.formName}
                            textInputProps={{
                                label: formMeta.fields.firstName.label,
                                required: true,
                                type: 'text',
                                autoComplete: 'given-name',
                                dataTestId: TEST_IDENTIFIER + '-' + formMeta.fields.firstName.name,
                            }}
                        />
                        <TextInputControlled
                            control={formProviderMethods.control}
                            name={formMeta.fields.lastName.name}
                            render={(textInput) => (
                                <FormLine bottomGap className="w-full flex-none lg:w-1/2">
                                    {textInput}
                                </FormLine>
                            )}
                            formName={formMeta.formName}
                            textInputProps={{
                                label: formMeta.fields.lastName.label,
                                required: true,
                                type: 'text',
                                autoComplete: 'family-name',
                                dataTestId: TEST_IDENTIFIER + '-' + formMeta.fields.lastName.name,
                            }}
                        />
                    </FormColumn>
                    <TextInputControlled
                        control={formProviderMethods.control}
                        name={formMeta.fields.telephone.name}
                        render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                        formName={formMeta.formName}
                        textInputProps={{
                            label: formMeta.fields.telephone.label,
                            required: true,
                            type: 'tel',
                            autoComplete: 'tel',
                            dataTestId: TEST_IDENTIFIER + '-' + formMeta.fields.telephone.name,
                        }}
                    />
                    <CheckboxControlled
                        name={formMeta.fields.newsletterSubscription.name}
                        control={formProviderMethods.control}
                        formName={formMeta.formName}
                        render={(checkbox) => (
                            <FormLine bottomGap>
                                <ChoiceFormLine>{checkbox}</ChoiceFormLine>
                            </FormLine>
                        )}
                        checkboxProps={{
                            label: formMeta.fields.newsletterSubscription.label,
                            dataTestId: TEST_IDENTIFIER + '-' + formMeta.fields.newsletterSubscription.name,
                        }}
                    />
                    <Heading type="h2">{t('Change password')}</Heading>
                    <PasswordInputControlled
                        control={formProviderMethods.control}
                        name={formMeta.fields.passwordOld.name}
                        render={(passwordInput) => (
                            <FormColumn>
                                <FormLine bottomGap className="w-full flex-none lg:w-1/2">
                                    {passwordInput}
                                </FormLine>
                            </FormColumn>
                        )}
                        formName={formMeta.formName}
                        passwordInputProps={{
                            label: formMeta.fields.passwordOld.label,
                            dataTestId: TEST_IDENTIFIER + '-' + formMeta.fields.passwordOld.name,
                        }}
                    />
                    <FormColumn>
                        <PasswordInputControlled
                            control={formProviderMethods.control}
                            name={formMeta.fields.passwordFirst.name}
                            render={(passwordInput) => (
                                <FormLine bottomGap className="w-full flex-none lg:w-1/2">
                                    {passwordInput}
                                </FormLine>
                            )}
                            formName={formMeta.formName}
                            passwordInputProps={{
                                label: formMeta.fields.passwordFirst.label,
                                dataTestId: TEST_IDENTIFIER + '-' + formMeta.fields.passwordFirst.name,
                            }}
                        />
                        <PasswordInputControlled
                            control={formProviderMethods.control}
                            name={formMeta.fields.passwordSecond.name}
                            render={(passwordInput) => (
                                <FormLine bottomGap className="w-full flex-none lg:w-1/2">
                                    {passwordInput}
                                </FormLine>
                            )}
                            formName={formMeta.formName}
                            passwordInputProps={{
                                label: formMeta.fields.passwordSecond.label,
                                dataTestId: TEST_IDENTIFIER + '-' + formMeta.fields.passwordSecond.name,
                            }}
                        />
                    </FormColumn>
                    {currentCustomerUser.companyCustomer && (
                        <>
                            <Heading type="h2">{t('Company information')}</Heading>
                            <TextInputControlled
                                control={formProviderMethods.control}
                                name={formMeta.fields.companyName.name}
                                render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                                formName={formMeta.formName}
                                textInputProps={{
                                    label: formMeta.fields.companyName.label,
                                    required: false,
                                    type: 'text',
                                    autoComplete: 'organization',
                                    dataTestId: TEST_IDENTIFIER + '-' + formMeta.fields.companyName.name,
                                }}
                            />
                            <TextInputControlled
                                control={formProviderMethods.control}
                                name={formMeta.fields.companyNumber.name}
                                render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                                formName={formMeta.formName}
                                textInputProps={{
                                    label: formMeta.fields.companyNumber.label,
                                    required: false,
                                    type: 'text',
                                    dataTestId: TEST_IDENTIFIER + '-' + formMeta.fields.companyNumber.name,
                                }}
                            />
                            <TextInputControlled
                                control={formProviderMethods.control}
                                name={formMeta.fields.companyTaxNumber.name}
                                render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                                formName={formMeta.formName}
                                textInputProps={{
                                    label: formMeta.fields.companyTaxNumber.label,
                                    required: false,
                                    type: 'text',
                                    dataTestId: TEST_IDENTIFIER + '-' + formMeta.fields.companyTaxNumber.name,
                                }}
                            />
                        </>
                    )}
                    <Heading type="h2">{t('Billing address')}</Heading>
                    <TextInputControlled
                        control={formProviderMethods.control}
                        name={formMeta.fields.street.name}
                        render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                        formName={formMeta.formName}
                        textInputProps={{
                            label: formMeta.fields.street.label,
                            required: true,
                            type: 'text',
                            autoComplete: 'street-address',
                            dataTestId: TEST_IDENTIFIER + '-' + formMeta.fields.street.name,
                        }}
                    />
                    <FormColumn>
                        <TextInputControlled
                            control={formProviderMethods.control}
                            name={formMeta.fields.city.name}
                            render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                            formName={formMeta.formName}
                            textInputProps={{
                                label: formMeta.fields.city.label,
                                required: true,
                                type: 'text',
                                autoComplete: 'address-level2',
                                dataTestId: TEST_IDENTIFIER + '-' + formMeta.fields.city.name,
                            }}
                        />
                        <TextInputControlled
                            control={formProviderMethods.control}
                            name={formMeta.fields.postcode.name}
                            render={(textInput) => (
                                <FormLine bottomGap className="w-full flex-none lg:w-[142px]">
                                    {textInput}
                                </FormLine>
                            )}
                            formName={formMeta.formName}
                            textInputProps={{
                                label: formMeta.fields.postcode.label,
                                required: true,
                                type: 'text',
                                autoComplete: 'postal-code',
                                dataTestId: TEST_IDENTIFIER + '-' + formMeta.fields.postcode.name,
                            }}
                        />
                    </FormColumn>
                    <FormLine bottomGap>
                        <Controller
                            name={formMeta.fields.country.name}
                            render={({ fieldState: { invalid, error }, field }) => (
                                <>
                                    <Select
                                        options={countriesAsSelectOptions}
                                        onChange={field.onChange}
                                        value={countriesAsSelectOptions.find(
                                            (option) => option.value === field.value.value,
                                        )}
                                        hasError={invalid}
                                        label={formMeta.fields.country.label}
                                        dataTestId={TEST_IDENTIFIER + '-' + formMeta.fields.country.name}
                                    />
                                    <FormLineError error={error} inputType="select" />
                                </>
                            )}
                        />
                    </FormLine>
                    {currentCustomerUser.deliveryAddresses.length > 0 && (
                        <>
                            <Heading type="h2">{t('Delivery addresses')}</Heading>
                            <FormLine bottomGap>
                                <AddressList
                                    deliveryAddresses={currentCustomerUser.deliveryAddresses}
                                    defaultDeliveryAddress={currentCustomerUser.defaultDeliveryAddress}
                                />
                            </FormLine>
                        </>
                    )}
                    <Button type="submit">{t('Save profile')}</Button>
                </Form>
            </FormProvider>
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
