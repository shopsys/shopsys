import { Form, FormBlockWrapper, FormContentWrapper, FormHeading } from 'components/Forms/Form/Form';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { Select } from 'components/Forms/Select/Select';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import {
    useCustomerChangeProfileForm,
    useCustomerChangeProfileFormMeta,
} from 'components/Pages/Customer/customerChangeProfileFormMeta';
import { useChangePersonalDataMutation } from 'graphql/requests/customer/mutations/ChangePersonalDataMutation.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import useTranslation from 'next-translate/useTranslation';
import { Controller, FormProvider, Path, SubmitHandler, UseFormReturn } from 'react-hook-form';
import { CurrentCustomerType } from 'types/customer';
import { CustomerChangeProfileFormType } from 'types/form';
import { CombinedError } from 'urql';
import { useCurrentCustomerUserPermissions } from 'utils/auth/useCurrentCustomerUserAuth';
import { getUserFriendlyErrors } from 'utils/errors/friendlyErrorMessageParser';
import { useErrorPopup } from 'utils/forms/useErrorPopup';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

type EditCustomerUserProfileContentProps = {
    currentCustomerUser: CurrentCustomerType;
};

export const EditCustomerUserProfileContent: FC<EditCustomerUserProfileContentProps> = ({ currentCustomerUser }) => {
    const { t } = useTranslation();
    const [, customerEditProfile] = useChangePersonalDataMutation();
    const { canManageCompanyData } = useCurrentCustomerUserPermissions();

    const [formProviderMethods] = useCustomerChangeProfileForm({
        ...currentCustomerUser,
        country: {
            label: currentCustomerUser.country.name,
            value: currentCustomerUser.country.code,
        },
    });
    const formMeta = useCustomerChangeProfileFormMeta(formProviderMethods);

    useErrorPopup(formProviderMethods, formMeta.fields, undefined, GtmMessageOriginType.other);

    const onSubmitCustomerChangeProfileFormHandler: SubmitHandler<CustomerChangeProfileFormType> = async (
        customerChangeProfileFormData,
        event,
    ) => {
        event?.preventDefault();

        onChangeProfileHandler(customerChangeProfileFormData);
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

        handleUpdateResult(
            changeProfileResult.data?.ChangePersonalData !== undefined,
            changeProfileResult.error,
            formProviderMethods,
            formMeta.messages,
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
                        {/*  <Controller
                            name={formMeta.fields.country.name}
                            render={({ fieldState: { invalid, error }, field }) => (
                                <>
                                    <Select
                                        hasError={invalid}
                                        isDisabled={!canManageCompanyData}
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
                        /> */}
                    </FormBlockWrapper>
                </FormContentWrapper>
            </Form>
        </FormProvider>
    );
};
