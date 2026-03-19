import { AnimateCollapseDivWithMargin } from 'components/Basic/Animations/AnimateCollapseDivWithMargin';
import { FormBlockWrapper, FormHeading } from 'components/Forms/Form/Form';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { CountrySelectControlled } from 'components/Forms/Select/CountrySelectControlled';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useRegistrationFormMeta } from 'components/Pages/Registration/registrationFormMeta';
import { AnimatePresence } from 'framer-motion';
import { useFormContext, useWatch } from 'react-hook-form';
import { RegistrationFormType } from 'types/form';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { RegistrationCompany } from './RegistrationCompany';
import { RegistrationCustomer } from './RegistrationCustomer';

export const RegistrationBillingAddress: FC = () => {
    const { t } = useTranslation();
    const formProviderMethods = useFormContext<RegistrationFormType>();
    const formMeta = useRegistrationFormMeta();
    const customerValue = useWatch({ name: formMeta.fields.customer.name, control: formProviderMethods.control });

    return (
        <div className="flex flex-col">
            <RegistrationCustomer />

            <FormBlockWrapper className="rounded-t-none pt-5">
                <FormHeading>{t('Billing address')}</FormHeading>

                <AnimatePresence initial={false}>
                    {customerValue === 'companyCustomer' && (
                        <AnimateCollapseDivWithMargin
                            className="flex! flex-col gap-5"
                            keyName="registration-company-data"
                        >
                            <RegistrationCompany />
                        </AnimateCollapseDivWithMargin>
                    )}
                </AnimatePresence>

                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.street.name}
                    textInputProps={{
                        label: formMeta.fields.street.label,
                        required: true,
                        type: 'text',
                        autoComplete: 'street-address',
                    }}
                />

                <FormColumn>
                    <TextInputControlled
                        control={formProviderMethods.control}
                        formName={formMeta.formName}
                        name={formMeta.fields.city.name}
                        width="wide"
                        textInputProps={{
                            label: formMeta.fields.city.label,
                            required: true,
                            type: 'text',
                            autoComplete: 'address-level2',
                        }}
                    />

                    <TextInputControlled
                        control={formProviderMethods.control}
                        formName={formMeta.formName}
                        name={formMeta.fields.postcode.name}
                        width="narrow"
                        textInputProps={{
                            label: formMeta.fields.postcode.label,
                            required: true,
                            type: 'text',
                            autoComplete: 'postal-code',
                            inputMode: 'numeric',
                        }}
                    />
                </FormColumn>

                <CountrySelectControlled
                    formName={formMeta.formName}
                    formProviderMethods={formProviderMethods}
                    label={formMeta.fields.country.label}
                    name={formMeta.fields.country.name}
                />
            </FormBlockWrapper>
        </div>
    );
};
