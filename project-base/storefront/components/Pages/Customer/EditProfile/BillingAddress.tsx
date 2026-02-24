import { CompanyCustomer } from './CompanyCustomer';
import { FormHeading, FormBlockWrapper } from 'components/Forms/Form/Form';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { Select } from 'components/Forms/Select/Select';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useCustomerChangeProfileFormMeta } from 'components/Pages/Customer/EditProfile/customerChangeProfileFormMeta';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useEffect } from 'react';
import { Controller, useFormContext } from 'react-hook-form';
import { CustomerChangeProfileFormType } from 'types/form';
import { useCountriesAsSelectOptions } from 'utils/countries/useCountriesAsSelectOptions';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const BillingAddress: FC<{ companyCustomer: boolean }> = ({ companyCustomer }) => {
    const { t } = useTranslation();
    const { canManageCompanyData } = useAuthorization();

    const formProviderMethods = useFormContext<CustomerChangeProfileFormType>();
    const formMeta = useCustomerChangeProfileFormMeta(formProviderMethods);
    const { setValue, getValues } = formProviderMethods;

    const countriesAsSelectOptions = useCountriesAsSelectOptions();
    const countryFieldName = formMeta.fields.country.name;

    useEffect(() => {
        if (countriesAsSelectOptions.length > 0) {
            const currentCountryValue = getValues(countryFieldName);
            const selectedCountry = countriesAsSelectOptions.find(
                (country) => country.value === currentCountryValue.value,
            );
            setValue(countryFieldName, selectedCountry ?? countriesAsSelectOptions[0], {
                shouldValidate: true,
            });
        }
    }, [countriesAsSelectOptions, countryFieldName, setValue, getValues]);

    return (
        <FormBlockWrapper>
            <FormHeading>{t('Billing address')}</FormHeading>

            {companyCustomer && <CompanyCustomer />}

            <TextInputControlled
                control={formProviderMethods.control}
                formName={formMeta.formName}
                name={formMeta.fields.street.name}
                render={(textInput) => <FormLine>{textInput}</FormLine>}
                textInputProps={{
                    label: formMeta.fields.street.label,
                    required: true,
                    type: 'text',
                    autoComplete: 'street-address',
                    disabled: !canManageCompanyData,
                }}
            />

            <FormColumn>
                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.city.name}
                    render={(textInput) => <FormLine className="col-span-3">{textInput}</FormLine>}
                    textInputProps={{
                        label: formMeta.fields.city.label,
                        required: true,
                        type: 'text',
                        autoComplete: 'address-level2',
                        disabled: !canManageCompanyData,
                    }}
                />
                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.postcode.name}
                    render={(textInput) => <FormLine className="col-start-4">{textInput}</FormLine>}
                    textInputProps={{
                        label: formMeta.fields.postcode.label,
                        required: true,
                        type: 'text',
                        autoComplete: 'postal-code',
                        disabled: !canManageCompanyData,
                        inputMode: 'numeric',
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
                                    isDisabled={!canManageCompanyData}
                                    label={formMeta.fields.country.label}
                                    options={countriesAsSelectOptions}
                                    tid={formMeta.formName + '-' + formMeta.fields.country.name}
                                    activeOption={
                                        field.value &&
                                        countriesAsSelectOptions.find((option) => option.value === field.value.value)
                                    }
                                    onSelectOption={field.onChange}
                                />
                                <FormLineError error={error} inputType="select" />
                            </>
                        )}
                    />
                </FormLine>
            </FormColumn>
        </FormBlockWrapper>
    );
};
