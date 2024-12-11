'use client';

import { RegistrationAddress } from './RegistrationAddress';
import { RegistrationCompany } from './RegistrationCompany';
import { RegistrationPassword } from './RegistrationPassword';
import { RegistrationUser } from './RegistrationUser';
import { useRegistrationForm, useRegistrationFormMeta } from './registrationFormMeta';
import { useRegistration } from 'app/_hooks/useRegistration';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper, FormHeading } from 'components/Forms/Form/Form';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { FormProvider, useWatch } from 'react-hook-form';
import { SelectOptionType } from 'types/selectOptions';

export type RegistrationFormProps = {
    formHeading: string;
    countries: SelectOptionType[];
};

export const RegistrationForm: FC<RegistrationFormProps> = ({ formHeading, countries }) => {
    const { t } = useTranslation();

    const [formProviderMethods, defaultValues] = useRegistrationForm({ preSelectedCountry: countries[0] });
    const formMeta = useRegistrationFormMeta(formProviderMethods);
    const customerValue = useWatch({ name: formMeta.fields.customer.name, control: formProviderMethods.control });

    const handleRegistration = useRegistration({ formMeta, defaultValues });

    // TODO: animation

    return (
        <FormProvider {...formProviderMethods}>
            <Form
                className="flex w-full justify-center"
                onSubmit={formProviderMethods.handleSubmit(handleRegistration)}
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
