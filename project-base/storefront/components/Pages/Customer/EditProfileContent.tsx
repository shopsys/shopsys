import { BillingAddress } from './EditProfile/BillingAddress';
import { ChangePassword } from './EditProfile/ChangePassword';
import { CompanyCustomer } from './EditProfile/CompanyCustomer';
import { DeliveryAddress } from './EditProfile/DeliveryAddress';
import { PersonalData } from './EditProfile/PersonalData';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form, FormButtonWrapper, FormContentWrapper } from 'components/Forms/Form/Form';
import {
    useCustomerChangeProfileForm,
    useCustomerChangeProfileFormMeta,
} from 'components/Pages/Customer/customerChangeProfileFormMeta';
import { useChangePasswordMutation } from 'graphql/requests/customer/mutations/ChangePasswordMutation.generated';
import { useChangePersonalDataMutation } from 'graphql/requests/customer/mutations/ChangePersonalDataMutation.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import useTranslation from 'next-translate/useTranslation';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { CurrentCustomerType } from 'types/customer';
import { CustomerChangeProfileFormType } from 'types/form';
import { handleFormErrors } from 'utils/forms/handleFormErrors';
import { useErrorPopup } from 'utils/forms/useErrorPopup';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

type EditProfileContentProps = {
    currentCustomerUser: CurrentCustomerType;
};

export const EditProfileContent: FC<EditProfileContentProps> = ({ currentCustomerUser }) => {
    const { t } = useTranslation();
    const [, customerEditProfile] = useChangePersonalDataMutation();
    const [, changePassword] = useChangePasswordMutation();

    const [formProviderMethods] = useCustomerChangeProfileForm({
        ...currentCustomerUser,
        country: {
            label: currentCustomerUser.country.name,
            value: currentCustomerUser.country.code,
        },
    });
    const formMeta = useCustomerChangeProfileFormMeta(formProviderMethods);

    const onSubmitCustomerChangeProfileFormHandler: SubmitHandler<CustomerChangeProfileFormType> = async (
        customerChangeProfileFormData,
        event,
    ) => {
        event?.preventDefault();

        const postponedProfileChangeAction = await onChangeProfileHandler(customerChangeProfileFormData);
        const passwordChangeResponse = await onChangePasswordHandler(customerChangeProfileFormData);

        if (!passwordChangeResponse.error) {
            postponedProfileChangeAction();
        }
    };

    const onChangeProfileHandler = async (
        customerChangeProfileFormData: CustomerChangeProfileFormType,
    ): Promise<() => void> => {
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
            return () => showSuccessMessage(formMeta.messages.success);
        }

        return () => handleFormErrors(changeProfileResult.error, formProviderMethods, t, formMeta.messages.error);
    };

    const onChangePasswordHandler = async (
        customerChangeProfileFormData: CustomerChangeProfileFormType,
    ): Promise<{ error: boolean }> => {
        if (
            customerChangeProfileFormData.newPassword === '' ||
            customerChangeProfileFormData.newPasswordConfirm === ''
        ) {
            return { error: false };
        }

        const changePasswordResult = await changePassword({
            email: customerChangeProfileFormData.email,
            oldPassword: customerChangeProfileFormData.oldPassword,
            newPassword: customerChangeProfileFormData.newPassword,
        });

        if (changePasswordResult.data?.ChangePassword !== undefined) {
            showSuccessMessage(t('Your password has been changed.'));
            return { error: false };
        }

        handleFormErrors(
            changePasswordResult.error,
            formProviderMethods,
            t,
            t('There was an error while changing your password'),
        );

        return {
            error: !!changePasswordResult.error,
        };
    };

    useErrorPopup(formProviderMethods, formMeta.fields, undefined, GtmMessageOriginType.other);

    return (
        <FormProvider {...formProviderMethods}>
            <Form onSubmit={formProviderMethods.handleSubmit(onSubmitCustomerChangeProfileFormHandler)}>
                <FormContentWrapper>
                    <PersonalData />

                    <ChangePassword
                        email={currentCustomerUser.email}
                        hasPasswordSet={currentCustomerUser.hasPasswordSet}
                    />

                    {currentCustomerUser.companyCustomer && <CompanyCustomer />}

                    <BillingAddress />

                    <DeliveryAddress
                        defaultDeliveryAddress={currentCustomerUser.defaultDeliveryAddress}
                        deliveryAddresses={currentCustomerUser.deliveryAddresses}
                    />

                    <FormButtonWrapper className="mt-0 pb-6">
                        <SubmitButton>{t('Save profile')}</SubmitButton>
                    </FormButtonWrapper>
                </FormContentWrapper>
            </Form>
        </FormProvider>
    );
};
