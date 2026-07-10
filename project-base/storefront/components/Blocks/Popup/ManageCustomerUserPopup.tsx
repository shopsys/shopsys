import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper, FormHeading } from 'components/Forms/Form/Form';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { PhoneNumberInputControlled } from 'components/Forms/PhonePrefixSelect/PhoneNumberInputControlled';
import { RadiobuttonGroup } from 'components/Forms/Radiobutton/RadiobuttonGroup';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { Popup } from 'components/Layout/Popup/Popup';
import {
    useCustomerUserManageProfileForm,
    useCustomerUserManageProfileFormMeta,
} from 'components/Pages/Customer/customerUserManageProfileFormMeta';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { getCustomerUser } from 'connectors/customer/CustomerUser';
import { TIDs } from 'cypress/tids';
import { TypeSimpleCustomerUserFragment } from 'graphql/requests/customer/fragments/SimpleCustomerUserFragment.generated';
import { useAddNewCustomerUserMutation } from 'graphql/requests/customer/mutations/AddNewCustomerUserMutation.generated';
import { useEditCustomerUserPersonalDataMutation } from 'graphql/requests/customer/mutations/EditCustomerUserPersonalDataMutation.generated';
import { useEffect } from 'react';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { useSessionStore } from 'store/useSessionStore';
import { twJoin } from 'tailwind-merge';
import { CustomerUserManageProfileFormType } from 'types/form';
import { useErrorHandler } from 'utils/errors/useErrorHandler';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';
import { useCustomerUserGroupsAsRadiobuttonOptions } from 'utils/user/useCustomerUserGroupsAsSelectOptions';

type ManageCustomerUserPopupProps = {
    customerUser?: TypeSimpleCustomerUserFragment;
    mode?: 'edit' | 'add';
};

export const ManageCustomerUserPopup: FC<ManageCustomerUserPopupProps> = ({ customerUser, mode = 'edit' }) => {
    const { t } = useTranslation();
    const [, customerEditUser] = useEditCustomerUserPersonalDataMutation();
    const [, customerAddUser] = useAddNewCustomerUserMutation();
    const closePortalContent = useSessionStore((s) => s.closePortalContent);
    const { canManageUsers, currentCustomerUserUuid: uuid } = useAuthorization();
    const customerUserData = getCustomerUser(customerUser);
    const isRoleGroupDisabled = !canManageUsers || (mode === 'edit' && customerUser?.uuid === uuid);
    const { customerUserRoleGroupsOptions, isFetching: isRoleGroupsFetching } =
        useCustomerUserGroupsAsRadiobuttonOptions(isRoleGroupDisabled);

    const customerUserDefaultFormData = {
        ...customerUserData,
        telephone: customerUserData.telephoneNumber,
        roleGroup: customerUser?.roleGroup.uuid ?? '',
    };
    const [formProviderMethods] = useCustomerUserManageProfileForm(customerUserDefaultFormData);
    const formMeta = useCustomerUserManageProfileFormMeta(mode);
    const handleError = useErrorHandler({
        form: formProviderMethods,
        customMessage: formMeta.messages.error,
    });

    const onSubmitCustomerUserManageProfileFormHandler: SubmitHandler<CustomerUserManageProfileFormType> = async (
        customerUserEditProfileFormData,
        event,
    ) => {
        event?.preventDefault();

        await onChangeProfileHandler(customerUserEditProfileFormData);
    };

    const onChangeProfileHandler = async (customerUserManageProfileFormData: CustomerUserManageProfileFormType) => {
        if (mode === 'edit') {
            const editUserResult = await customerEditUser({
                input: {
                    customerUserUuid: customerUser?.uuid ?? null,
                    firstName: customerUserManageProfileFormData.firstName,
                    lastName: customerUserManageProfileFormData.lastName,
                    telephone: {
                        prefix: customerUserManageProfileFormData.telephonePrefix,
                        countryCode: customerUserManageProfileFormData.telephonePrefixCountryCode || '',
                        number: customerUserManageProfileFormData.telephone,
                    },
                    roleGroupUuid: customerUserManageProfileFormData.roleGroup,
                    newsletterSubscription: false,
                },
            });

            closePortalContent();

            if (editUserResult.data?.EditCustomerUserPersonalData !== undefined) {
                showSuccessMessage(formMeta.messages.success);
                closePortalContent();
            }

            handleError(editUserResult.error);

            return;
        }

        const addUserResult = await customerAddUser({
            input: {
                email: customerUserManageProfileFormData.email,
                firstName: customerUserManageProfileFormData.firstName,
                lastName: customerUserManageProfileFormData.lastName,
                telephone: {
                    prefix: customerUserManageProfileFormData.telephonePrefix,
                    countryCode: customerUserManageProfileFormData.telephonePrefixCountryCode || '',
                    number: customerUserManageProfileFormData.telephone,
                },
                roleGroupUuid: customerUserManageProfileFormData.roleGroup,
                newsletterSubscription: false,
            },
        });

        if (addUserResult.data?.AddNewCustomerUser !== undefined) {
            showSuccessMessage(formMeta.messages.success);
            closePortalContent();
        }

        handleError(addUserResult.error);
    };

    useEffect(() => {
        const firstRoleOption = customerUserRoleGroupsOptions[0]?.value;
        const hasNoDefaultRole = mode === 'add' && !isRoleGroupsFetching && firstRoleOption;

        if (hasNoDefaultRole) {
            formProviderMethods.setValue('roleGroup', firstRoleOption);
        }
    }, [customerUserRoleGroupsOptions, isRoleGroupsFetching, mode, formProviderMethods]);

    const popupTitle = mode === 'edit' ? t('Edit customer user') : t('Add customer user');

    return (
        <Popup className="vl:w-auto w-11/12 lg:w-4/5" contentClassName="overflow-y-auto" title={popupTitle}>
            <FormProvider {...formProviderMethods}>
                <Form
                    formName={formMeta.formName}
                    onSubmit={formProviderMethods.handleSubmit(onSubmitCustomerUserManageProfileFormHandler)}
                >
                    <FormContentWrapper>
                        <FormBlockWrapper>
                            <FormHeading>{t('Personal data')}</FormHeading>

                            <TextInputControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.email.name}
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
                                    width="half"
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
                                    width="half"
                                    textInputProps={{
                                        label: formMeta.fields.lastName.label,
                                        required: true,
                                        type: 'text',
                                        autoComplete: 'family-name',
                                    }}
                                />
                            </FormColumn>

                            <PhoneNumberInputControlled
                                formName={formMeta.formName}
                                formProviderMethods={formProviderMethods}
                                prefixCountryCodeName={formMeta.fields.telephonePrefixCountryCode.name}
                                prefixName={formMeta.fields.telephonePrefix.name}
                                telephoneLabel={formMeta.fields.telephone.label}
                                telephoneName={formMeta.fields.telephone.name}
                            />
                        </FormBlockWrapper>

                        <FormBlockWrapper>
                            <FormHeading>{formMeta.fields.roleGroup.label}</FormHeading>

                            <div
                                aria-label={t('Select role group', { ns: 'accessibility' })}
                                data-tid={`${formMeta.formName}-${formMeta.fields.roleGroup.name}`}
                                className={twJoin(
                                    'flex flex-col gap-2 vl:gap-4',
                                    isRoleGroupDisabled ? 'pointer-events-none opacity-50' : undefined,
                                )}
                            >
                                {isRoleGroupsFetching ? (
                                    <div className="flex flex-col gap-2">
                                        {Array.from({ length: 5 }).map((_, index) => (
                                            <Skeleton key={index} className="h-6 w-1/2 rounded" />
                                        ))}
                                    </div>
                                ) : (
                                    <RadiobuttonGroup
                                        control={formProviderMethods.control}
                                        formName={formMeta.formName}
                                        name={formMeta.fields.roleGroup.name}
                                        radiobuttons={customerUserRoleGroupsOptions}
                                        render={(radiobutton, key) => <span key={key}>{radiobutton}</span>}
                                    />
                                )}
                            </div>
                        </FormBlockWrapper>

                        <FormButtonWrapper>
                            <SubmitButton data-tid={TIDs.customer_users_manage_submit_button}>
                                {mode === 'edit' ? t('Save user') : t('Add user')}
                            </SubmitButton>
                        </FormButtonWrapper>
                    </FormContentWrapper>
                </Form>
            </FormProvider>
        </Popup>
    );
};
