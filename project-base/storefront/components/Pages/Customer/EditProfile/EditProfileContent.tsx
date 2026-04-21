import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form, FormButtonWrapper, FormContentWrapper } from 'components/Forms/Form/Form';
import {
    useCustomerChangeProfileForm,
    useCustomerChangeProfileFormMeta,
} from 'components/Pages/Customer/EditProfile/customerChangeProfileFormMeta';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { TIDs } from 'cypress/tids';
import { useChangeCompanyDataMutation } from 'graphql/requests/customer/mutations/ChangeCompanyDataMutation.generated';
import { useChangePersonalDataMutation } from 'graphql/requests/customer/mutations/ChangePersonalDataMutation.generated';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { CurrentCustomerType } from 'types/customer';
import { CustomerChangeProfileFormType } from 'types/form';
import { useErrorHandler } from 'utils/errors/useErrorHandler';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';
import { BillingAddress } from './BillingAddress';
import { DeliveryAddress } from './DeliveryAddress';
import { NewsletterSubscription } from './NewsletterSubscription';
import { PersonalData } from './PersonalData';

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
        telephonePrefix: currentCustomerUser.telephonePrefix,
        telephonePrefixCountryCode: currentCustomerUser.telephonePrefixCountryCode,
        telephone: currentCustomerUser.telephoneNumber,
        country: {
            label: currentCustomerUser.country.name,
            value: currentCustomerUser.country.code,
        },
    });
    const formMeta = useCustomerChangeProfileFormMeta();
    const isSubmitting = formProviderMethods.formState.isSubmitting;
    const handleError = useErrorHandler({
        form: formProviderMethods,
        customMessage: formMeta.messages.error,
    });

    const onSubmitCustomerChangeProfileFormHandler: SubmitHandler<CustomerChangeProfileFormType> = async (
        customerChangeProfileFormData,
        event,
    ) => {
        event?.preventDefault();

        await onChangeProfileHandler(customerChangeProfileFormData);
    };

    const onChangeProfileHandler = async (customerChangeProfileFormData: CustomerChangeProfileFormType) => {
        const changeProfileResult = await customerEditProfile({
            input: {
                firstName: customerChangeProfileFormData.firstName,
                lastName: customerChangeProfileFormData.lastName,
                telephone: {
                    prefix: customerChangeProfileFormData.telephonePrefix,
                    countryCode: customerChangeProfileFormData.telephonePrefixCountryCode || '',
                    number: customerChangeProfileFormData.telephone,
                },
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

            handleError(changeCompanyDataResult.error);
        }

        if (changeProfileResult.data?.ChangePersonalData !== undefined && isCompanyDataChanged) {
            showSuccessMessage(formMeta.messages.success);
        }

        handleError(changeProfileResult.error);
    };

    return (
        <FormProvider {...formProviderMethods}>
            <Form
                formName={formMeta.formName}
                onSubmit={formProviderMethods.handleSubmit(onSubmitCustomerChangeProfileFormHandler)}
            >
                <FormContentWrapper>
                    <PersonalData />

                    <BillingAddress companyCustomer={currentCustomerUser.companyCustomer} />

                    <DeliveryAddress
                        defaultDeliveryAddress={currentCustomerUser.defaultDeliveryAddress}
                        deliveryAddresses={currentCustomerUser.deliveryAddresses}
                    />

                    <NewsletterSubscription />

                    {canManagePersonalData && (
                        <FormButtonWrapper>
                            <SubmitButton
                                aria-label={t('Submit form to save changes in your profile', { ns: 'accessibility' })}
                                data-tid={TIDs.edit_profile_save_button}
                                hasDisabledLook={isSubmitting}
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
