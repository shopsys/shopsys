import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper } from 'components/Forms/Form/Form';
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
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { getCustomerUser } from 'connectors/customer/CustomerUser';
import { TypeSimpleCustomerUserFragment } from 'graphql/requests/customer/fragments/SimpleCustomerUserFragment.generated';
import { useAddNewCustomerUserMutation } from 'graphql/requests/customer/mutations/AddNewCustomerUserMutation.generated';
import { useEditCustomerUserPersonalDataMutation } from 'graphql/requests/customer/mutations/EditCustomerUserPersonalDataMutation.generated';
import useTranslation from 'next-translate/useTranslation';
import { Controller, FormProvider, SubmitHandler } from 'react-hook-form';
import { useSessionStore } from 'store/useSessionStore';
import { CustomerUserManageProfileFormType } from 'types/form';
import { handleFormErrors } from 'utils/forms/handleFormErrors';
import { useScrollToFirstError } from 'utils/forms/useScrollToFirstError';
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
    const { canManageCompanyData, currentCustomerUserUuid: uuid } = useAuthorization();
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
                    newsletterSubscription: false,
                },
            });

            updatePortalContent(null);

            if (editUserResult.data?.EditCustomerUserPersonalData !== undefined) {
                showSuccessMessage(formMeta.messages.success);
                updatePortalContent(null);
            }

            handleFormErrors(editUserResult.error, formProviderMethods, t, formMeta.messages.error);

            return;
        }

        const addUserResult = await customerAddUser({
            input: {
                email: customerUserManageProfileFormData.email,
                firstName: customerUserManageProfileFormData.firstName,
                lastName: customerUserManageProfileFormData.lastName,
                telephone: customerUserManageProfileFormData.telephone,
                roleGroupUuid: customerUserManageProfileFormData.roleGroup.value,
                newsletterSubscription: false,
            },
        });

        if (addUserResult.data?.AddNewCustomerUser !== undefined) {
            showSuccessMessage(formMeta.messages.success);
            updatePortalContent(null);
        }

        handleFormErrors(addUserResult.error, formProviderMethods, t, formMeta.messages.error);
    };

    useScrollToFirstError(formMeta.formName, formProviderMethods);

    return (
        <Popup className="vl:w-auto w-11/12 lg:w-4/5" contentClassName="overflow-y-auto" title={t('Personal data')}>
            <FormProvider {...formProviderMethods}>
                <Form onSubmit={formProviderMethods.handleSubmit(onSubmitCustomerUserManageProfileFormHandler)}>
                    <FormContentWrapper>
                        <FormBlockWrapper>
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
                                render={({ fieldState: { error }, field }) => (
                                    <>
                                        <Select
                                            isRequired
                                            ariaLabel={t('Select role group')}
                                            label={formMeta.fields.roleGroup.label}
                                            options={customerUserRoleGroupsAsSelectOptions}
                                            tid={formMeta.formName + '-' + formMeta.fields.roleGroup.name}
                                            activeOption={customerUserRoleGroupsAsSelectOptions.find(
                                                (option) => option.value === field.value.value,
                                            )}
                                            isDisabled={
                                                !canManageCompanyData ||
                                                (mode === 'edit' && customerUser?.uuid === uuid)
                                            }
                                            onSelectOption={field.onChange}
                                        />
                                        <FormLineError error={error} inputType="select" />
                                    </>
                                )}
                            />
                            <FormButtonWrapper>
                                <SubmitButton
                                    aria-label={
                                        mode === 'edit'
                                            ? t('Submit form to save user changes')
                                            : t('Submit form to add new user')
                                    }
                                >
                                    {mode === 'edit' ? t('Save user') : t('Add user')}
                                </SubmitButton>
                            </FormButtonWrapper>
                        </FormBlockWrapper>
                    </FormContentWrapper>
                </Form>
            </FormProvider>
        </Popup>
    );
};
