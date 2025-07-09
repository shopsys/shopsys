import { BillingAddress } from './BillingAddress';
import { CompanyCustomer } from './CompanyCustomer';
import { DeliveryAddress } from './DeliveryAddress';
import { PersonalData } from './PersonalData';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form, FormButtonWrapper, FormContentWrapper } from 'components/Forms/Form/Form';
import {
    useCustomerChangeProfileForm,
    useCustomerChangeProfileFormMeta,
} from 'components/Pages/Customer/EditProfile/customerChangeProfileFormMeta';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useChangeCompanyDataMutation } from 'graphql/requests/customer/mutations/ChangeCompanyDataMutation.generated';
import { useChangePersonalDataMutation } from 'graphql/requests/customer/mutations/ChangePersonalDataMutation.generated';
import useTranslation from 'next-translate/useTranslation';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { CurrentCustomerType } from 'types/customer';
import { CustomerChangeProfileFormType } from 'types/form';
import { handleFormErrors } from 'utils/forms/handleFormErrors';
import { useScrollToFirstError } from 'utils/forms/useScrollToFirstError';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

type EditProfileContentProps = {
    currentCustomerUser: CurrentCustomerType;
};

export const EditProfileContent: FC<EditProfileContentProps> = ({ currentCustomerUser }) => {
    const { t } = useTranslation();
    const [, customerEditProfile] = useChangePersonalDataMutation();
    const [, companyEditProfile] = useChangeCompanyDataMutation();
    const { canManageCompanyData, canManagePersonalData } = useAuthorization();

    const [formProviderMethods] = useCustomerChangeProfileForm({
        ...currentCustomerUser,
        country: {
            label: currentCustomerUser.country.name,
            value: currentCustomerUser.country.code,
        },
    });
    const formMeta = useCustomerChangeProfileFormMeta(formProviderMethods);
    const isSubmitting = formProviderMethods.formState.isSubmitting;

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
                firstName: customerChangeProfileFormData.firstName,
                lastName: customerChangeProfileFormData.lastName,
                telephone: customerChangeProfileFormData.telephone,
                newsletterSubscription: customerChangeProfileFormData.newsletterSubscription,
            },
        });

        let isCompanyDataChanged = true;

        if (canManageCompanyData) {
            const changeCompanyDataResult = await companyEditProfile({
                input: {
                    billingAddressUuid: currentCustomerUser.billingAddressUuid,
                    companyCustomer: customerChangeProfileFormData.companyCustomer,
                    companyName: customerChangeProfileFormData.companyName,
                    companyNumber: customerChangeProfileFormData.companyNumber,
                    companyTaxNumber: customerChangeProfileFormData.companyTaxNumber,
                    street: customerChangeProfileFormData.street,
                    city: customerChangeProfileFormData.city,
                    country: customerChangeProfileFormData.country.value,
                    postcode: customerChangeProfileFormData.postcode,
                },
            });

            isCompanyDataChanged = changeCompanyDataResult.data?.ChangeCompanyData !== undefined;

            handleFormErrors(changeCompanyDataResult.error, formProviderMethods, t, formMeta.messages.error);
        }

        if (changeProfileResult.data?.ChangePersonalData !== undefined && isCompanyDataChanged) {
            showSuccessMessage(formMeta.messages.success);
        }

        handleFormErrors(changeProfileResult.error, formProviderMethods, t, formMeta.messages.error);
    };

    useScrollToFirstError(formMeta.formName, formProviderMethods);

    return (
        <FormProvider {...formProviderMethods}>
            <Form onSubmit={formProviderMethods.handleSubmit(onSubmitCustomerChangeProfileFormHandler)}>
                <FormContentWrapper>
                    <PersonalData />

                    {currentCustomerUser.companyCustomer && <CompanyCustomer />}

                    <BillingAddress />

                    <DeliveryAddress
                        defaultDeliveryAddress={currentCustomerUser.defaultDeliveryAddress}
                        deliveryAddresses={currentCustomerUser.deliveryAddresses}
                    />

                    {canManagePersonalData && (
                        <FormButtonWrapper className="mt-0 pb-6">
                            <SubmitButton
                                aria-label={t('Submit form to save changes in your profile')}
                                isDisabled={isSubmitting}
                            >
                                {t('Save profile')}
                            </SubmitButton>
                        </FormButtonWrapper>
                    )}
                </FormContentWrapper>
            </Form>
        </FormProvider>
    );
};
