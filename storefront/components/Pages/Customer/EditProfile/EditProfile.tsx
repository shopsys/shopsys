import { Controller, FormProvider, SubmitHandler } from 'react-hook-form';
import { showErrorMessage, showSuccessMessage } from 'components/Helpers/Toasts';
import { useChangePasswordMutationApi, useChangePersonalDataMutationApi } from 'graphql/generated';
import {
    useCustomerChangeProfileForm,
    useCustomerChangeProfileFormMeta,
} from 'components/Pages/Customer/EditProfile/formMeta';
import AddressList from 'components/Pages/Customer/AddressList';
import Button from 'components/Forms/Button';
import Checkbox from 'components/Forms/Checkbox';
import ChoiceFormLine from 'components/Forms/Lib/ChoiceFormLine';
import { CurrentCustomerType } from 'types/customer';
import { CustomerChangeProfileFormType } from 'types/form';
import { EditProfileTextStyled } from 'components/Pages/Customer/EditProfile/EditProfile.style';
import ErrorPopup from 'components/Forms/Lib/ErrorPopup';
import { FC } from 'react';
import Form from 'components/Forms/Form';
import FormColumn from 'components/Forms/Lib/FormColumn';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import Heading from 'components/Basic/Heading';
import Select from 'components/Forms/Select';
import TextInput from 'components/Forms/TextInput';
import { useAuth } from 'hooks/auth/UseAuth';
import { useCountriesAsSelectOptions } from 'connectors/country/Country';
import { useHandleErrorPopupVisibility } from 'hooks/forms/UseHandleErrorPopupVisibility';
import { useHandleFormErrors } from 'hooks/forms/UseHandleFormErrors';
import { useHandleFormSuccessfulSubmit } from 'hooks/forms/UseHandleFormSuccessfulSubmit';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type EditProfilePageProps = {
    currentCustomerUser: CurrentCustomerType;
};

const EditProfile: FC<EditProfilePageProps> = (props) => {
    const testIdentifier = 'form-edit-profile';
    const t = useTypedTranslationFunction();
    const [customerEditProfileResult, customerEditProfile] = useChangePersonalDataMutationApi();

    const [formProviderMethods, defaultFormValues] = useCustomerChangeProfileForm({
        ...props.currentCustomerUser,
        country: {
            label: props.currentCustomerUser.country.name,
            value: props.currentCustomerUser.country.code,
        },
    });
    const formMeta = useCustomerChangeProfileFormMeta(formProviderMethods);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useHandleErrorPopupVisibility(formProviderMethods);
    const countrySelectOptions = useCountriesAsSelectOptions();
    const [, changePassword] = useChangePasswordMutationApi();
    const [, [, logout]] = useAuth();

    useHandleFormErrors(customerEditProfileResult.error, formProviderMethods, formMeta.messages.error);
    useHandleFormSuccessfulSubmit(
        customerEditProfileResult,
        formProviderMethods,
        defaultFormValues,
        () => showSuccessMessage(formMeta.messages.success),
        { blur: true, reset: false },
    );

    const onChangeProfileHandler: SubmitHandler<CustomerChangeProfileFormType> = async (data, event) => {
        event?.preventDefault();

        await customerEditProfile({ input: { ...data, country: data.country.value } });

        // matching both passwords is checked on Yup
        if (data.passwordFirst !== '' && data.passwordSecond !== '') {
            onChangePasswordHandler(data);
        }
    };

    const onChangePasswordHandler = async (data: CustomerChangeProfileFormType) => {
        const changePasswordResult = await changePassword({
            email: data.email,
            oldPassword: data.passwordOld,
            newPassword: data.passwordFirst,
        });

        if (changePasswordResult.error !== undefined) {
            showErrorMessage(t('There was an error while changing your password'));
        }
        if (changePasswordResult.data?.ChangePassword.email !== undefined) {
            showSuccessMessage(t('Your password has been changed. You will be signed out.'));
            setTimeout(() => logout(), 2000);
        }
    };

    return (
        <>
            <FormProvider {...formProviderMethods}>
                <Form onSubmit={formProviderMethods.handleSubmit(onChangeProfileHandler)} noValidate>
                    <Heading type="h2">{t('Personal data')}</Heading>
                    <FormLine bottomGap={true}>
                        <Controller
                            name={formMeta.fields.email.name}
                            render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                <>
                                    <TextInput
                                        id={formMeta.formName + '-' + formMeta.fields.email.name}
                                        name={formMeta.fields.email.name}
                                        label={formMeta.fields.email.label}
                                        required={false}
                                        disabled={true}
                                        type="text"
                                        isTouched={isTouched}
                                        hasError={invalid}
                                        fieldRef={field}
                                        data-testid={testIdentifier + '-' + formMeta.fields.email.name}
                                    />
                                    <FormLineError error={error} inputType="text-input" />
                                </>
                            )}
                        />
                    </FormLine>
                    <FormLine bottomGap={true}>
                        <EditProfileTextStyled>
                            {t(
                                'To prevent the possibility of account theft, it is necessary to deal with the change of e-mail individually. If your e-mail address has changed, please contact us.',
                            )}
                        </EditProfileTextStyled>
                    </FormLine>
                    <FormColumn>
                        <FormLine bottomGap={true} width="100%" lg="50%">
                            <Controller
                                name={formMeta.fields.firstName.name}
                                render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                    <>
                                        <TextInput
                                            id={formMeta.formName + '-' + formMeta.fields.firstName.name}
                                            name={formMeta.fields.firstName.name}
                                            label={formMeta.fields.firstName.label}
                                            required={true}
                                            type="text"
                                            isTouched={isTouched}
                                            hasError={invalid}
                                            fieldRef={field}
                                            data-testid={testIdentifier + '-' + formMeta.fields.firstName.name}
                                        />
                                        <FormLineError error={error} inputType="text-input" />
                                    </>
                                )}
                            />
                        </FormLine>
                        <FormLine bottomGap={true} width="100%" lg="50%">
                            <Controller
                                name={formMeta.fields.lastName.name}
                                render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                    <>
                                        <TextInput
                                            id={formMeta.formName + '-' + formMeta.fields.lastName.name}
                                            name={formMeta.fields.lastName.name}
                                            label={formMeta.fields.lastName.label}
                                            required={true}
                                            type="text"
                                            isTouched={isTouched}
                                            hasError={invalid}
                                            fieldRef={field}
                                            data-testid={testIdentifier + '-' + formMeta.fields.lastName.name}
                                        />
                                        <FormLineError error={error} inputType="text-input" />
                                    </>
                                )}
                            />
                        </FormLine>
                    </FormColumn>
                    <FormLine bottomGap={true}>
                        <Controller
                            name={formMeta.fields.telephone.name}
                            render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                <>
                                    <TextInput
                                        id={formMeta.formName + '-' + formMeta.fields.telephone.name}
                                        name={formMeta.fields.telephone.name}
                                        label={formMeta.fields.telephone.label}
                                        required={true}
                                        type="text"
                                        isTouched={isTouched}
                                        hasError={invalid}
                                        fieldRef={field}
                                        data-testid={testIdentifier + '-' + formMeta.fields.telephone.name}
                                    />
                                    <FormLineError error={error} inputType="text-input" />
                                </>
                            )}
                        />
                    </FormLine>
                    <FormLine bottomGap={true}>
                        <ChoiceFormLine>
                            <Controller
                                name={formMeta.fields.newsletterSubscription.name}
                                render={({ field }) => (
                                    <Checkbox
                                        id={formMeta.formName + '-' + formMeta.fields.newsletterSubscription.name}
                                        name={formMeta.fields.newsletterSubscription.name}
                                        label={formMeta.fields.newsletterSubscription.label}
                                        fieldRef={field}
                                        data-testid={testIdentifier + '-' + formMeta.fields.newsletterSubscription.name}
                                    />
                                )}
                            />
                        </ChoiceFormLine>
                    </FormLine>
                    <Heading type="h2">{t('Change password')}</Heading>
                    <FormColumn>
                        <FormLine bottomGap={true} width="100%" lg="50%">
                            <Controller
                                name={formMeta.fields.passwordOld.name}
                                render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                    <>
                                        <TextInput
                                            id={formMeta.formName + '-' + formMeta.fields.passwordOld.name}
                                            name={formMeta.fields.passwordOld.name}
                                            label={formMeta.fields.passwordOld.label}
                                            required={true}
                                            type="password"
                                            isTouched={isTouched}
                                            hasError={invalid}
                                            fieldRef={field}
                                            data-testid={testIdentifier + '-' + formMeta.fields.passwordOld.name}
                                        />
                                        <FormLineError error={error} inputType="text-input-password" />
                                    </>
                                )}
                            />
                        </FormLine>
                    </FormColumn>
                    <FormColumn>
                        <FormLine bottomGap={true} width="100%" lg="50%">
                            <Controller
                                name={formMeta.fields.passwordFirst.name}
                                render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                    <>
                                        <TextInput
                                            id={formMeta.formName + '-' + formMeta.fields.passwordFirst.name}
                                            name={formMeta.fields.passwordFirst.name}
                                            label={formMeta.fields.passwordFirst.label}
                                            required={true}
                                            type="password"
                                            isTouched={isTouched}
                                            hasError={invalid}
                                            fieldRef={field}
                                            data-testid={testIdentifier + '-' + formMeta.fields.passwordFirst.name}
                                        />
                                        <FormLineError error={error} inputType="text-input-password" />
                                    </>
                                )}
                            />
                        </FormLine>
                        <FormLine bottomGap={true} width="100%" lg="50%">
                            <Controller
                                name={formMeta.fields.passwordSecond.name}
                                render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                    <>
                                        <TextInput
                                            id={formMeta.formName + '-' + formMeta.fields.passwordSecond.name}
                                            name={formMeta.fields.passwordSecond.name}
                                            label={formMeta.fields.passwordSecond.label}
                                            required={true}
                                            type="password"
                                            isTouched={isTouched}
                                            hasError={invalid}
                                            fieldRef={field}
                                            data-testid={testIdentifier + '-' + formMeta.fields.passwordSecond.name}
                                        />
                                        <FormLineError error={error} inputType="text-input-password" />
                                    </>
                                )}
                            />
                        </FormLine>
                    </FormColumn>
                    {props.currentCustomerUser.companyCustomer && (
                        <>
                            <Heading type="h2">{t('Company information')}</Heading>
                            <FormLine bottomGap={true}>
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
                                                data-testid={testIdentifier + '-' + formMeta.fields.companyName.name}
                                            />
                                            <FormLineError error={error} inputType="text-input" />
                                        </>
                                    )}
                                />
                            </FormLine>
                            <FormLine bottomGap={true}>
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
                                                data-testid={testIdentifier + '-' + formMeta.fields.companyNumber.name}
                                            />
                                            <FormLineError error={error} inputType="text-input" />
                                        </>
                                    )}
                                />
                            </FormLine>
                            <FormLine bottomGap={true}>
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
                                                data-testid={
                                                    testIdentifier + '-' + formMeta.fields.companyTaxNumber.name
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
                    <FormLine bottomGap={true}>
                        <Controller
                            name={formMeta.fields.street.name}
                            render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                <>
                                    <TextInput
                                        id={formMeta.formName + '-' + formMeta.fields.street.name}
                                        name={formMeta.fields.street.name}
                                        label={formMeta.fields.street.label}
                                        required={true}
                                        type="text"
                                        isTouched={isTouched}
                                        hasError={invalid}
                                        fieldRef={field}
                                        data-testid={testIdentifier + '-' + formMeta.fields.street.name}
                                    />
                                    <FormLineError error={error} inputType="text-input" />
                                </>
                            )}
                        />
                    </FormLine>
                    <FormColumn>
                        <FormLine bottomGap={true}>
                            <Controller
                                name={formMeta.fields.city.name}
                                render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                    <>
                                        <TextInput
                                            id={formMeta.formName + '-' + formMeta.fields.city.name}
                                            name={formMeta.fields.city.name}
                                            label={formMeta.fields.city.label}
                                            required={true}
                                            type="text"
                                            isTouched={isTouched}
                                            hasError={invalid}
                                            fieldRef={field}
                                            data-testid={testIdentifier + '-' + formMeta.fields.city.name}
                                        />
                                        <FormLineError error={error} inputType="text-input" />
                                    </>
                                )}
                            />
                        </FormLine>
                        <FormLine bottomGap={true} width="100%" lg="142px">
                            <Controller
                                name={formMeta.fields.postcode.name}
                                render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                    <>
                                        <TextInput
                                            id={formMeta.formName + '-' + formMeta.fields.postcode.name}
                                            name={formMeta.fields.postcode.name}
                                            label={formMeta.fields.postcode.label}
                                            required={true}
                                            type="text"
                                            isTouched={isTouched}
                                            hasError={invalid}
                                            fieldRef={field}
                                            data-testid={testIdentifier + '-' + formMeta.fields.postcode.name}
                                        />
                                        <FormLineError error={error} inputType="text-input" />
                                    </>
                                )}
                            />
                        </FormLine>
                    </FormColumn>
                    <FormLine bottomGap={true}>
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
                                        data-testid={testIdentifier + '-' + formMeta.fields.country.name}
                                    />
                                    <FormLineError error={error} inputType="select" />
                                </>
                            )}
                        />
                    </FormLine>
                    {props.currentCustomerUser.deliveryAddresses.length > 0 && (
                        <>
                            <Heading type="h2">{t('Delivery addresses')}</Heading>
                            <FormLine bottomGap={true}>
                                <AddressList
                                    deliveryAddresses={props.currentCustomerUser.deliveryAddresses}
                                    defaultDeliveryAddress={props.currentCustomerUser.defaultDeliveryAddress}
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
            />
        </>
    );
};

export default EditProfile;
