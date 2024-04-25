import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { Select } from 'components/Forms/Select/Select';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/contactInformationFormMeta';
import useTranslation from 'next-translate/useTranslation';
import { Controller, useFormContext } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';
import { SelectOptionType } from 'types/selectOptions';
import { useCountriesAsSelectOptions } from 'utils/countries/useCountriesAsSelectOptions';

export const ContactInformationAddress: FC = () => {
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);
    const { t } = useTranslation();
    const formProviderMethods = useFormContext<ContactInformation>();
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const countriesAsSelectOptions = useCountriesAsSelectOptions();

    return (
        <>
            <div className="h4 mb-3">{t('Billing address')}</div>
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
                        onChange: (event) => updateContactInformation({ street: event.currentTarget.value }),
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
                        onChange: (event) => updateContactInformation({ city: event.currentTarget.value }),
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
                        onChange: (event) => updateContactInformation({ postcode: event.currentTarget.value }),
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
                                onChange={(...data) => {
                                    field.onChange(...data);
                                    updateContactInformation({
                                        country: data[0] as SelectOptionType,
                                    });
                                }}
                            />
                            <FormLineError error={error} inputType="select" />
                        </>
                    )}
                />
            </FormLine>
        </>
    );
};
