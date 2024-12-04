'use client';

import { RegistrationAddress } from './RegistrationAddress';
import { RegistrationCompany } from './RegistrationCompany';
import { RegistrationPassword } from './RegistrationPassword';
import { RegistrationUser } from './RegistrationUser';
import { useRegistrationForm, useRegistrationFormMeta } from './registrationFormMeta';
import { registrationAction } from 'app/_actions/registrationAction';
import { useHandleActionsAfterRegistration } from 'app/_hooks/useHandleActionsAfterRegistration';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper, FormHeading } from 'components/Forms/Form/Form';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine';
import { TIDs } from 'cypress/tids';
import { TypeRegistrationMutationVariables } from 'graphql/requests/registration/mutations/RegistrationMutation.ssr';
import useTranslation from 'next-translate/useTranslation';
import { FormProvider, SubmitHandler, useWatch } from 'react-hook-form';
import { usePersistStore } from 'store/usePersistStore';
import { RegistrationFormType } from 'types/form';
import { SelectOptionType } from 'types/selectOptions';
import { blurInput } from 'utils/forms/blurInput';
import { clearForm } from 'utils/forms/clearForm';
import { handleFormErrors } from 'utils/forms/handleFormErrors';

export type RegistrationFormProps = {
    formHeading: string;
    countries: SelectOptionType[];
};

export const RegistrationForm: FC<RegistrationFormProps> = ({ formHeading, countries }) => {
    const { t } = useTranslation();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const productListUuids = usePersistStore((s) => s.productListUuids);
    const handleActionsAfterRegistration = useHandleActionsAfterRegistration();

    const [formProviderMethods, defaultValues] = useRegistrationForm({ preSelectedCountry: countries[0] });
    const formMeta = useRegistrationFormMeta(formProviderMethods);
    const customerValue = useWatch({ name: formMeta.fields.customer.name, control: formProviderMethods.control });

    const onRegistrationHandler: SubmitHandler<RegistrationFormType> = async (formData) => {
        blurInput();

        const registrationData: TypeRegistrationMutationVariables = {
            input: {
                email: formData.email,
                firstName: formData.firstName,
                lastName: formData.lastName,
                telephone: formData.telephone,
                password: formData.password,
                companyCustomer: formData.customer === 'companyCustomer',
                companyName: formData.companyName,
                companyNumber: formData.companyNumber,
                companyTaxNumber: formData.companyTaxNumber,
                street: formData.street,
                city: formData.city,
                country: formData.country.value,
                postcode: formData.postcode,
                newsletterSubscription: formData.newsletterSubscription,
                cartUuid: cartUuid,
                billingAddressUuid: null,
                productListsUuids: Object.values(productListUuids),
            },
        };

        const { error, showCartMergeInfo } = await registrationAction(registrationData);

        if (error) {
            handleFormErrors(error, formProviderMethods, t, formMeta.messages.error);

            return;
        }

        handleActionsAfterRegistration(showCartMergeInfo);

        clearForm(error, formProviderMethods, defaultValues);
    };

    // TODO: animation

    return (
        <FormProvider {...formProviderMethods}>
            <Form
                className="flex w-full justify-center"
                onSubmit={formProviderMethods.handleSubmit(onRegistrationHandler)}
            >
                <FormContentWrapper>
                    <FormHeading>{formHeading}</FormHeading>

                    <RegistrationUser />

                    {/* <AnimatePresence initial={false}>
                        {customerValue === 'companyCustomer' && (
                            <AnimateCollapseDiv className="!flex flex-col" keyName="registration-company-data">
                                <RegistrationCompany />
                            </AnimateCollapseDiv>
                        )}
                    </AnimatePresence> */}

                    {customerValue === 'companyCustomer' && <RegistrationCompany />}

                    <RegistrationPassword />

                    <RegistrationAddress countries={countries} />

                    <FormBlockWrapper>
                        <CheckboxControlled
                            control={formProviderMethods.control}
                            formName={formMeta.formName}
                            name={formMeta.fields.gdprAgreement.name}
                            render={(checkbox) => <ChoiceFormLine>{checkbox}</ChoiceFormLine>}
                            checkboxProps={{
                                label: formMeta.fields.gdprAgreement.label,
                            }}
                        />

                        <CheckboxControlled
                            control={formProviderMethods.control}
                            formName={formMeta.formName}
                            name={formMeta.fields.newsletterSubscription.name}
                            render={(checkbox) => <ChoiceFormLine>{checkbox}</ChoiceFormLine>}
                            checkboxProps={{
                                label: formMeta.fields.newsletterSubscription.label,
                            }}
                        />

                        <FormButtonWrapper>
                            <SubmitButton tid={TIDs.registration_submit_button}>{t('Sign up')}</SubmitButton>
                        </FormButtonWrapper>
                    </FormBlockWrapper>
                </FormContentWrapper>
            </Form>
        </FormProvider>
    );
};
