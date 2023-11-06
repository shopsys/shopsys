import { Heading } from 'components/Basic/Heading/Heading';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { Select } from 'components/Forms/Select/Select';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/contactInformationFormMeta';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { useCountriesQueryApi } from 'graphql/generated';
import { mapCountriesToSelectOptions } from 'helpers/mappers/country';
import useTranslation from 'next-translate/useTranslation';
import { useEffect, useMemo } from 'react';
import { Controller, useFormContext, useWatch } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';

export const ContactInformationAddress: FC = () => {
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);
    const { t } = useTranslation();
    const formProviderMethods = useFormContext<ContactInformation>();
    const { setValue } = formProviderMethods;
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const [{ data: countriesData }] = useCountriesQueryApi();
    const user = useCurrentCustomerData();
    const countriesAsSelectOptions = useMemo(
        () => mapCountriesToSelectOptions(countriesData?.countries),
        [countriesData?.countries],
    );
    const [countryValue] = useWatch({
        name: [formMeta.fields.country.name],
        control: formProviderMethods.control,
    });

    useEffect(() => {
        if (countriesAsSelectOptions.length && !countryValue.value) {
            const selectedCountryOption = countriesAsSelectOptions.find(
                (option) => option.value === user?.country.code,
            );

            setValue(formMeta.fields.country.name, selectedCountryOption || countriesAsSelectOptions[0], {
                shouldValidate: true,
            });
        }
    }, [countriesAsSelectOptions, countryValue, formMeta.fields.country.name]);

    if (countriesAsSelectOptions.length === 0) {
        return null;
    }

    return (
        <>
            <Heading type="h3">{t('Billing address')}</Heading>
            <FormLine className="flex-none lg:w-[65%]">
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
                        onBlur: (event) => updateContactInformation({ street: event.currentTarget.value }),
                    }}
                />
            </FormLine>
            <FormColumn className="lg:w-[calc(65%+0.75rem)]">
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
                        onBlur: (event) => updateContactInformation({ city: event.currentTarget.value }),
                    }}
                />
                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.postcode.name}
                    render={(textInput) => (
                        <FormLine bottomGap className="w-full flex-none lg:w-[142px]">
                            {textInput}
                        </FormLine>
                    )}
                    textInputProps={{
                        label: formMeta.fields.postcode.label,
                        required: true,
                        type: 'text',
                        autoComplete: 'postal-code',
                        onBlur: (event) => updateContactInformation({ postcode: event.currentTarget.value }),
                    }}
                />
            </FormColumn>
            <FormLine className="flex-none lg:w-[65%]">
                <Controller
                    name={formMeta.fields.country.name}
                    render={({ fieldState: { invalid, error }, field }) => (
                        <>
                            <Select
                                hasError={invalid}
                                label={formMeta.fields.country.label}
                                options={countriesAsSelectOptions}
                                value={countriesAsSelectOptions.find((option) => option.value === field.value.value)}
                                onChange={field.onChange}
                            />
                            <FormLineError
                                dataTestId={formMeta.formName + '-' + formMeta.fields.country.name + '-error'}
                                error={error}
                                inputType="select"
                            />
                        </>
                    )}
                />
            </FormLine>
        </>
    );
};
