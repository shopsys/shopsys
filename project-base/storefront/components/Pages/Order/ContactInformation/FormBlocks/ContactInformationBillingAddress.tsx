import { AnimateCollapseDivWithMargin } from 'components/Basic/Animations/AnimateCollapseDivWithMargin';
import { FormBlockWrapper, FormHeading } from 'components/Forms/Form/Form';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { CountrySelectControlled } from 'components/Forms/Select/CountrySelectControlled';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/contactInformationFormMeta';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { AnimatePresence } from 'framer-motion';
import { useFormContext, useWatch } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { ContactInformationCompany } from './ContactInformationCompany';
import { ContactInformationCustomer } from './ContactInformationCustomer';

export const ContactInformationBillingAddress: FC = () => {
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);
    const { t } = useTranslation();
    const formProviderMethods = useFormContext<ContactInformation>();
    const formMeta = useContactInformationFormMeta();
    const { canManageCompanyData } = useAuthorization();
    const customerValue = useWatch({ name: formMeta.fields.customer.name, control: formProviderMethods.control });

    return (
        <div className="flex flex-col">
            <ContactInformationCustomer />

            <FormBlockWrapper className="rounded-t-none pt-5">
                <FormHeading>{t('Billing address')}</FormHeading>

                <AnimatePresence initial={false}>
                    {customerValue === 'companyCustomer' && (
                        <AnimateCollapseDivWithMargin
                            className="flex! flex-col gap-5"
                            keyName="company-contact-information"
                        >
                            <ContactInformationCompany />
                        </AnimateCollapseDivWithMargin>
                    )}
                </AnimatePresence>

                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.street.name}
                    textInputProps={{
                        disabled: !canManageCompanyData,
                        label: formMeta.fields.street.label,
                        required: true,
                        type: 'text',
                        autoComplete: 'street-address',
                        onChange: (event) => updateContactInformation({ street: event.currentTarget.value }),
                    }}
                />

                <FormColumn>
                    <TextInputControlled
                        control={formProviderMethods.control}
                        formName={formMeta.formName}
                        name={formMeta.fields.city.name}
                        width="wide"
                        textInputProps={{
                            disabled: !canManageCompanyData,
                            label: formMeta.fields.city.label,
                            required: true,
                            type: 'text',
                            autoComplete: 'address-level2',
                            onChange: (event) => updateContactInformation({ city: event.currentTarget.value }),
                        }}
                    />

                    <TextInputControlled
                        control={formProviderMethods.control}
                        formName={formMeta.formName}
                        name={formMeta.fields.postcode.name}
                        width="narrow"
                        textInputProps={{
                            disabled: !canManageCompanyData,
                            label: formMeta.fields.postcode.label,
                            required: true,
                            type: 'text',
                            autoComplete: 'postal-code',
                            inputMode: 'numeric',
                            onChange: (event) => updateContactInformation({ postcode: event.currentTarget.value }),
                        }}
                    />
                </FormColumn>

                <CountrySelectControlled
                    formName={formMeta.formName}
                    formProviderMethods={formProviderMethods}
                    label={formMeta.fields.country.label}
                    name={formMeta.fields.country.name}
                    onCountryChange={(country) => updateContactInformation({ country })}
                />
            </FormBlockWrapper>
        </div>
    );
};
