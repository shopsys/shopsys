import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper, FormHeading } from 'components/Forms/Form/Form';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { Select } from 'components/Forms/Select/Select';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { Popup } from 'components/Layout/Popup/Popup';
import {
    useCustomerUserManageProfileForm,
    useCustomerUserManageProfileFormMeta,
} from 'components/Pages/Customer/customerUserManageProfileFormMeta';
import { getCustomerUser } from 'connectors/customer/CustomerUser';
import { TypeSimpleCustomerUserFragment } from 'graphql/requests/customer/fragments/SimpleCustomerUserFragment.generated';
import { useAddNewCustomerUserMutation } from 'graphql/requests/customer/mutations/AddNewCustomerUserMutation.generated';
import { useEditCustomerUserPersonalDataMutation } from 'graphql/requests/customer/mutations/EditCustomerUserPersonalDataMutation.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import useTranslation from 'next-translate/useTranslation';
import { Controller, FormProvider, Path, SubmitHandler, UseFormReturn } from 'react-hook-form';
import { useSessionStore } from 'store/useSessionStore';
import { CustomerUserManageProfileFormType } from 'types/form';
import { CombinedError } from 'urql';
import { useCurrentCustomerUserPermissions } from 'utils/auth/useCurrentCustomerUserPermissions';
import { getUserFriendlyErrors } from 'utils/errors/friendlyErrorMessageParser';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';
import { useCustomerUserGroupsAsSelectOptions } from 'utils/user/useCustomerUserGroupsAsSelectOptions';

type ManageCustomerUserPopupProps = {
    customerUser?: TypeSimpleCustomerUserFragment;
    mode?: 'edit' | 'add';
};

export const ManageCustomerUserPopup: FC<ManageCustomerUserPopupProps> = ({ customerUser, mode = 'edit' }) => {
    const { t } = useTranslation();
    const [, customerEditUser] = useEditCustomerUserPersonalDataMutation();
    const [, customerAddUser] = useAddNewCustomerUserMutation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const { canManageProfile, currentCustomerUserUuid: uuid } = useCurrentCustomerUserPermissions();
    const customerUserRoleGroupsAsSelectOptions = useCustomerUserGroupsAsSelectOptions();
    const customerUserData = getCustomerUser(customerUser);

    const customerUserDefaultFormData = {
        ...customerUserData,
        roleGroup: {
            label: customerUser?.roleGroup.name ?? '',
            value: customerUser?.roleGroup.uuid ?? '',
        },
    };

    const [formProviderMethods] = useCustomerUserManageProfileForm(customerUserDefaultFormData);
    const formMeta = useCustomerUserManageProfileFormMeta(formProviderMethods, mode);

    const onSubmitCustomerUserManageProfileFormHandler: SubmitHandler<CustomerUserManageProfileFormType> = async (
        customerUserEditProfileFormData,
        event,
    ) => {
        event?.preventDefault();

        onChangeProfileHandler(customerUserEditProfileFormData);
    };

    const onChangeProfileHandler = async (customerUserManageProfileFormData: CustomerUserManageProfileFormType) => {
        if (mode === 'edit') {
            const editUserResult = await customerEditUser({
                input: {
                    customerUserUuid: customerUser?.uuid ?? null,
                    firstName: customerUserManageProfileFormData.firstName,
                    lastName: customerUserManageProfileFormData.lastName,
                    telephone: customerUserManageProfileFormData.telephone,
                    roleGroupUuid: customerUserManageProfileFormData.roleGroup.value,
                },
            });

            handleUpdateResult(
                editUserResult.data?.EditCustomerUserPersonalData !== undefined,
                editUserResult.error,
                formProviderMethods,
                formMeta.messages,
            );
            return;
        }

        const addUserResult = await customerAddUser({
            input: {
                email: customerUserManageProfileFormData.email,
                firstName: customerUserManageProfileFormData.firstName,
                lastName: customerUserManageProfileFormData.lastName,
                telephone: customerUserManageProfileFormData.telephone,
                roleGroupUuid: customerUserManageProfileFormData.roleGroup.value,
            },
        });

        handleUpdateResult(
            addUserResult.data?.AddNewCustomerUser !== undefined,
            addUserResult.error,
            formProviderMethods,
            formMeta.messages,
        );
    };

    const handleUpdateResult = (
        isResultOk: boolean,
        error: CombinedError | undefined,
        formProviderMethods: UseFormReturn<CustomerUserManageProfileFormType>,
        messages: { success?: string; error?: string },
        callbacks?: { success?: () => void; error?: () => void },
    ) => {
        if (isResultOk) {
            if (messages.success !== undefined) {
                showSuccessMessage(messages.success);
            }
            if (callbacks?.success !== undefined) {
                callbacks.success();
            }
        }

        if (error === undefined) {
            updatePortalContent(null);
            return;
        }

        const { userError, applicationError } = getUserFriendlyErrors(error, t);

        if (applicationError !== undefined) {
            if (messages.error !== undefined) {
                showErrorMessage(applicationError.message || messages.error, GtmMessageOriginType.other);
            }
            if (callbacks?.error !== undefined) {
                callbacks.error();
            }
        }

        if (userError?.validation !== undefined) {
            for (const fieldName in userError.validation) {
                formProviderMethods.setError(
                    fieldName as Path<CustomerUserManageProfileFormType>,
                    userError.validation[fieldName],
                );
            }
        }
    };

    return (
        <Popup className="w-11/12 overflow-visible lg:w-4/5 vl:w-auto">
            <FormProvider {...formProviderMethods}>
                <Form onSubmit={formProviderMethods.handleSubmit(onSubmitCustomerUserManageProfileFormHandler)}>
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
                                    required: mode === 'add',
                                    disabled: mode === 'edit',
                                    type: 'email',
                                    autoComplete: 'email',
                                }}
                            />
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
                            <Controller
                                name={formMeta.fields.roleGroup.name}
                                render={({ fieldState: { invalid, error }, field }) => (
                                    <>
                                        <Select
                                            required
                                            hasError={invalid}
                                            id={formMeta.formName + '-' + formMeta.fields.roleGroup.name}
                                            label={formMeta.fields.roleGroup.label}
                                            options={customerUserRoleGroupsAsSelectOptions}
                                            isDisabled={
                                                !canManageProfile || (mode === 'edit' && customerUser?.uuid === uuid)
                                            }
                                            value={customerUserRoleGroupsAsSelectOptions.find(
                                                (option) => option.value === field.value.value,
                                            )}
                                            onChange={field.onChange}
                                        />
                                        <FormLineError error={error} inputType="select" />
                                    </>
                                )}
                            />
                            <FormButtonWrapper>
                                <SubmitButton>{mode === 'edit' ? t('Save user') : t('Add user')}</SubmitButton>
                            </FormButtonWrapper>
                        </FormBlockWrapper>
                    </FormContentWrapper>
                </Form>
            </FormProvider>
        </Popup>
    );
};
