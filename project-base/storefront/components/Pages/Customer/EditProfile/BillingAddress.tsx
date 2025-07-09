import { FormHeading, FormBlockWrapper } from 'components/Forms/Form/Form';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { Select } from 'components/Forms/Select/Select';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useCustomerChangeProfileFormMeta } from 'components/Pages/Customer/EditProfile/customerChangeProfileFormMeta';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import useTranslation from 'next-translate/useTranslation';
import { useEffect } from 'react';
import { Controller, useFormContext } from 'react-hook-form';
import { CustomerChangeProfileFormType } from 'types/form';
import { useCountriesAsSelectOptions } from 'utils/countries/useCountriesAsSelectOptions';

export const BillingAddress: FC = () => {
    const { t } = useTranslation();
    const { canManageCompanyData } = useAuthorization();

    const formProviderMethods = useFormContext<CustomerChangeProfileFormType>();
    const formMeta = useCustomerChangeProfileFormMeta(formProviderMethods);
    const { setValue } = formProviderMethods;

    const countriesAsSelectOptions = useCountriesAsSelectOptions();

    useEffect(() => {
        if (countriesAsSelectOptions.length > 0) {
            const userCountryValue = formProviderMethods.getValues(formMeta.fields.country.name);
            const selectedCountry = countriesAsSelectOptions.find(
                (country) => country.value === userCountryValue.value,
            );
            setValue(formMeta.fields.country.name, selectedCountry ?? countriesAsSelectOptions[0], {
                shouldValidate: true,
            });
        }
    }, [countriesAsSelectOptions, formMeta.fields.country.name, setValue]);

    return (
        <FormBlockWrapper>
            <FormHeading>{t('Billing address')}</FormHeading>
            <TextInputControlled
                control={formProviderMethods.control}
                formName={formMeta.formName}
                name={formMeta.fields.street.name}
                render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
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
                    render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
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
                    render={(textInput) => (
                        <FormLine bottomGap isSmallInput>
                            {textInput}
                        </FormLine>
                    )}
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
            <FormLine bottomGap>
                <Controller
                    name={formMeta.fields.country.name}
                    render={({ fieldState: { error }, field }) => (
                        <>
                            <Select
                                isRequired
                                ariaLabel={t('Select country')}
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
        </FormBlockWrapper>
    );
};
