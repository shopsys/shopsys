import { Heading } from 'components/Basic/Heading/Heading';
import { Button } from 'components/Forms/Button/Button';
import { Checkbox } from 'components/Forms/Checkbox/Checkbox';
import { Form } from 'components/Forms/Form/Form';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine/ChoiceFormLine';
import { ErrorPopup } from 'components/Forms/Lib/ErrorPopup/ErrorPopup';
import { FormColumn } from 'components/Forms/Lib/FormColumn/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError/FormLineError';
import { Select } from 'components/Forms/Select/Select';
import { TextInput } from 'components/Forms/TextInput/TextInput';
import { showErrorMessage, showSuccessMessage } from 'components/Helpers/Toasts';
import { AddressList } from 'components/Pages/Customer/AddressList/AddressList';
import { EditProfileTextStyled } from 'components/Pages/Customer/EditProfile/EditProfileContent.style';
import {
    useCustomerChangeProfileForm,
    useCustomerChangeProfileFormMeta,
} from 'components/Pages/Customer/EditProfile/formMeta';
import { useCountriesAsSelectOptions } from 'connectors/country/Country';
import { getUserFriendlyErrors } from 'connectors/lib/friendlyErrorMessageParser';
import { useChangePasswordMutationApi, useChangePersonalDataMutationApi } from 'graphql/generated';
import { useHandleErrorPopupVisibility } from 'hooks/forms/useHandleErrorPopupVisibility';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC } from 'react';
import { Controller, FormProvider, Path, SubmitHandler, UseFormReturn } from 'react-hook-form';
import { CurrentCustomerType } from 'types/customer';
import { CustomerChangeProfileFormType } from 'types/form';
import { CombinedError } from 'urql';

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
    const [isErrorPopupVisible, setErrorPopupVisibility] = useHandleErrorPopupVisibility(formProviderMethods);
    const countrySelectOptions = useCountriesAsSelectOptions();
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
                showErrorMessage(messages.error, 'other');
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
                    <FormLine bottomGap>
                        <Controller
                            name={formMeta.fields.email.name}
                            render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                <>
                                    <TextInput
                                        id={formMeta.formName + '-' + formMeta.fields.email.name}
                                        name={formMeta.fields.email.name}
                                        label={formMeta.fields.email.label}
                                        required={false}
                                        disabled
                                        type="text"
                                        isTouched={isTouched}
                                        hasError={invalid}
                                        fieldRef={field}
                                        testIdentifier={TEST_IDENTIFIER + '-' + formMeta.fields.email.name}
                                    />
                                    <FormLineError error={error} inputType="text-input" />
                                </>
                            )}
                        />
                    </FormLine>
                    <FormLine bottomGap>
                        <EditProfileTextStyled>
                            {t(
                                'To prevent the possibility of account theft, it is necessary to deal with the change of e-mail individually. If your e-mail address has changed, please contact us.',
                            )}
                        </EditProfileTextStyled>
                    </FormLine>
                    <FormColumn>
                        <FormLine bottomGap width="100%" lg="50%">
                            <Controller
                                name={formMeta.fields.firstName.name}
                                render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                    <>
                                        <TextInput
                                            id={formMeta.formName + '-' + formMeta.fields.firstName.name}
                                            name={formMeta.fields.firstName.name}
                                            label={formMeta.fields.firstName.label}
                                            required
                                            type="text"
                                            isTouched={isTouched}
                                            hasError={invalid}
                                            fieldRef={field}
                                            testIdentifier={TEST_IDENTIFIER + '-' + formMeta.fields.firstName.name}
                                        />
                                        <FormLineError error={error} inputType="text-input" />
                                    </>
                                )}
                            />
                        </FormLine>
                        <FormLine bottomGap width="100%" lg="50%">
                            <Controller
                                name={formMeta.fields.lastName.name}
                                render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                    <>
                                        <TextInput
                                            id={formMeta.formName + '-' + formMeta.fields.lastName.name}
                                            name={formMeta.fields.lastName.name}
                                            label={formMeta.fields.lastName.label}
                                            required
                                            type="text"
                                            isTouched={isTouched}
                                            hasError={invalid}
                                            fieldRef={field}
                                            testIdentifier={TEST_IDENTIFIER + '-' + formMeta.fields.lastName.name}
                                        />
                                        <FormLineError error={error} inputType="text-input" />
                                    </>
                                )}
                            />
                        </FormLine>
                    </FormColumn>
                    <FormLine bottomGap>
                        <Controller
                            name={formMeta.fields.telephone.name}
                            render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                <>
                                    <TextInput
                                        id={formMeta.formName + '-' + formMeta.fields.telephone.name}
                                        name={formMeta.fields.telephone.name}
                                        label={formMeta.fields.telephone.label}
                                        required
                                        type="text"
                                        isTouched={isTouched}
                                        hasError={invalid}
                                        fieldRef={field}
                                        testIdentifier={TEST_IDENTIFIER + '-' + formMeta.fields.telephone.name}
                                    />
                                    <FormLineError error={error} inputType="text-input" />
                                </>
                            )}
                        />
                    </FormLine>
                    <FormLine bottomGap>
                        <ChoiceFormLine>
                            <Controller
                                name={formMeta.fields.newsletterSubscription.name}
                                render={({ field }) => (
                                    <Checkbox
                                        id={formMeta.formName + '-' + formMeta.fields.newsletterSubscription.name}
                                        name={formMeta.fields.newsletterSubscription.name}
                                        label={formMeta.fields.newsletterSubscription.label}
                                        fieldRef={field}
                                        testIdentifier={
                                            TEST_IDENTIFIER + '-' + formMeta.fields.newsletterSubscription.name
                                        }
                                    />
                                )}
                            />
                        </ChoiceFormLine>
                    </FormLine>
                    <Heading type="h2">{t('Change password')}</Heading>
                    <FormColumn>
                        <FormLine bottomGap width="100%" lg="50%">
                            <Controller
                                name={formMeta.fields.passwordOld.name}
                                render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                    <>
                                        <TextInput
                                            id={formMeta.formName + '-' + formMeta.fields.passwordOld.name}
                                            name={formMeta.fields.passwordOld.name}
                                            label={formMeta.fields.passwordOld.label}
                                            required
                                            type="password"
                                            isTouched={isTouched}
                                            hasError={invalid}
                                            fieldRef={field}
                                            testIdentifier={TEST_IDENTIFIER + '-' + formMeta.fields.passwordOld.name}
                                        />
                                        <FormLineError error={error} inputType="text-input-password" />
                                    </>
                                )}
                            />
                        </FormLine>
                    </FormColumn>
                    <FormColumn>
                        <FormLine bottomGap width="100%" lg="50%">
                            <Controller
                                name={formMeta.fields.passwordFirst.name}
                                render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                    <>
                                        <TextInput
                                            id={formMeta.formName + '-' + formMeta.fields.passwordFirst.name}
                                            name={formMeta.fields.passwordFirst.name}
                                            label={formMeta.fields.passwordFirst.label}
                                            required
                                            type="password"
                                            isTouched={isTouched}
                                            hasError={invalid}
                                            fieldRef={field}
                                            testIdentifier={TEST_IDENTIFIER + '-' + formMeta.fields.passwordFirst.name}
                                        />
                                        <FormLineError error={error} inputType="text-input-password" />
                                    </>
                                )}
                            />
                        </FormLine>
                        <FormLine bottomGap width="100%" lg="50%">
                            <Controller
                                name={formMeta.fields.passwordSecond.name}
                                render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                    <>
                                        <TextInput
                                            id={formMeta.formName + '-' + formMeta.fields.passwordSecond.name}
                                            name={formMeta.fields.passwordSecond.name}
                                            label={formMeta.fields.passwordSecond.label}
                                            required
                                            type="password"
                                            isTouched={isTouched}
                                            hasError={invalid}
                                            fieldRef={field}
                                            testIdentifier={TEST_IDENTIFIER + '-' + formMeta.fields.passwordSecond.name}
                                        />
                                        <FormLineError error={error} inputType="text-input-password" />
                                    </>
                                )}
                            />
                        </FormLine>
                    </FormColumn>
                    {currentCustomerUser.companyCustomer && (
                        <>
                            <Heading type="h2">{t('Company information')}</Heading>
                            <FormLine bottomGap>
                                <Controller
                                    name={formMeta.fields.companyName.name}
                                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                        <>
                                            <TextInput
                                                id={formMeta.formName + '-' + formMeta.fields.companyName.name}
                                                name={formMeta.fields.companyName.name}
                                                label={formMeta.fields.companyName.label}
                                                required={false}
                                                type="text"
                                                isTouched={isTouched}
                                                hasError={invalid}
                                                fieldRef={field}
                                                testIdentifier={
                                                    TEST_IDENTIFIER + '-' + formMeta.fields.companyName.name
                                                }
                                            />
                                            <FormLineError error={error} inputType="text-input" />
                                        </>
                                    )}
                                />
                            </FormLine>
                            <FormLine bottomGap>
                                <Controller
                                    name={formMeta.fields.companyNumber.name}
                                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                        <>
                                            <TextInput
                                                id={formMeta.formName + '-' + formMeta.fields.companyNumber.name}
                                                name={formMeta.fields.companyNumber.name}
                                                label={formMeta.fields.companyNumber.label}
                                                required={false}
                                                type="text"
                                                isTouched={isTouched}
                                                hasError={invalid}
                                                fieldRef={field}
                                                testIdentifier={
                                                    TEST_IDENTIFIER + '-' + formMeta.fields.companyNumber.name
                                                }
                                            />
                                            <FormLineError error={error} inputType="text-input" />
                                        </>
                                    )}
                                />
                            </FormLine>
                            <FormLine bottomGap>
                                <Controller
                                    name={formMeta.fields.companyTaxNumber.name}
                                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                        <>
                                            <TextInput
                                                id={formMeta.formName + '-' + formMeta.fields.companyTaxNumber.name}
                                                name={formMeta.fields.companyTaxNumber.name}
                                                label={formMeta.fields.companyTaxNumber.label}
                                                required={false}
                                                type="text"
                                                isTouched={isTouched}
                                                hasError={invalid}
                                                fieldRef={field}
                                                testIdentifier={
                                                    TEST_IDENTIFIER + '-' + formMeta.fields.companyTaxNumber.name
                                                }
                                            />
                                            <FormLineError error={error} inputType="text-input" />
                                        </>
                                    )}
                                />
                            </FormLine>
                        </>
                    )}
                    <Heading type="h2">{t('Billing address')}</Heading>
                    <FormLine bottomGap>
                        <Controller
                            name={formMeta.fields.street.name}
                            render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                <>
                                    <TextInput
                                        id={formMeta.formName + '-' + formMeta.fields.street.name}
                                        name={formMeta.fields.street.name}
                                        label={formMeta.fields.street.label}
                                        required
                                        type="text"
                                        isTouched={isTouched}
                                        hasError={invalid}
                                        fieldRef={field}
                                        testIdentifier={TEST_IDENTIFIER + '-' + formMeta.fields.street.name}
                                    />
                                    <FormLineError error={error} inputType="text-input" />
                                </>
                            )}
                        />
                    </FormLine>
                    <FormColumn>
                        <FormLine bottomGap>
                            <Controller
                                name={formMeta.fields.city.name}
                                render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                    <>
                                        <TextInput
                                            id={formMeta.formName + '-' + formMeta.fields.city.name}
                                            name={formMeta.fields.city.name}
                                            label={formMeta.fields.city.label}
                                            required
                                            type="text"
                                            isTouched={isTouched}
                                            hasError={invalid}
                                            fieldRef={field}
                                            testIdentifier={TEST_IDENTIFIER + '-' + formMeta.fields.city.name}
                                        />
                                        <FormLineError error={error} inputType="text-input" />
                                    </>
                                )}
                            />
                        </FormLine>
                        <FormLine bottomGap width="100%" lg="142px">
                            <Controller
                                name={formMeta.fields.postcode.name}
                                render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                    <>
                                        <TextInput
                                            id={formMeta.formName + '-' + formMeta.fields.postcode.name}
                                            name={formMeta.fields.postcode.name}
                                            label={formMeta.fields.postcode.label}
                                            required
                                            type="text"
                                            isTouched={isTouched}
                                            hasError={invalid}
                                            fieldRef={field}
                                            testIdentifier={TEST_IDENTIFIER + '-' + formMeta.fields.postcode.name}
                                        />
                                        <FormLineError error={error} inputType="text-input" />
                                    </>
                                )}
                            />
                        </FormLine>
                    </FormColumn>
                    <FormLine bottomGap>
                        <Controller
                            name={formMeta.fields.country.name}
                            render={({ fieldState: { invalid, error }, field }) => (
                                <>
                                    <Select
                                        options={countrySelectOptions}
                                        onChange={field.onChange}
                                        value={countrySelectOptions.find((option) => option.value === field.value)}
                                        hasError={invalid}
                                        fieldRef={field}
                                        label={formMeta.fields.country.label}
                                        data-testid={TEST_IDENTIFIER + '-' + formMeta.fields.country.name}
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
            <ErrorPopup
                isVisible={isErrorPopupVisible}
                onCloseCallback={() => setErrorPopupVisibility(false)}
                fields={formMeta.fields}
                origin="other"
            />
        </>
    );
};
