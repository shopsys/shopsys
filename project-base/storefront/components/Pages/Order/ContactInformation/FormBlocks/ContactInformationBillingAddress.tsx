import { ContactInformationCompany } from './ContactInformationCompany';
import { ContactInformationCustomer } from './ContactInformationCustomer';
import { AnimateCollapseDivWithMargin } from 'components/Basic/Animations/AnimateCollapseDivWithMargin';
import { FormBlockWrapper, FormHeading } from 'components/Forms/Form/Form';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { Select } from 'components/Forms/Select/Select';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/contactInformationFormMeta';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { AnimatePresence } from 'framer-motion';
import { Controller, useFormContext, useWatch } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';
import { SelectOptionType } from 'types/selectOptions';
import { useCountriesAsSelectOptions } from 'utils/countries/useCountriesAsSelectOptions';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const ContactInformationBillingAddress: FC = () => {
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);
    const { t } = useTranslation();
    const formProviderMethods = useFormContext<ContactInformation>();
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const countriesAsSelectOptions = useCountriesAsSelectOptions();
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
                    render={(textInput) => <FormLine>{textInput}</FormLine>}
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
                        render={(textInput) => <FormLine className="col-span-3">{textInput}</FormLine>}
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
                        render={(textInput) => <FormLine className="col-start-4">{textInput}</FormLine>}
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

                <FormColumn>
                    <FormLine className="col-span-3">
                        <Controller
                            name={formMeta.fields.country.name}
                            render={({ fieldState: { error }, field }) => (
                                <>
                                    <Select
                                        isRequired
                                        ariaLabel={t('Select country', { ns: 'accessibility' })}
                                        label={formMeta.fields.country.label}
                                        options={countriesAsSelectOptions}
                                        tid={formMeta.formName + '-' + formMeta.fields.country.name}
                                        activeOption={countriesAsSelectOptions.find(
                                            (option) => option.value === field.value.value,
                                        )}
                                        onSelectOption={(...selectOnChangeEventData) => {
                                            field.onChange(...selectOnChangeEventData);
                                            updateContactInformation({
                                                country: selectOnChangeEventData[0] as SelectOptionType,
                                            });
                                        }}
                                    />
                                    <FormLineError error={error} inputType="select" />
                                </>
                            )}
                        />
                    </FormLine>
                </FormColumn>
            </FormBlockWrapper>
        </div>
    );
};
