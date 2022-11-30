import { Heading } from 'components/Basic/Heading/Heading';
import { FormColumn } from 'components/Forms/Lib/FormColumn/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError/FormLineError';
import { Select } from 'components/Forms/Select/Select';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useRegistrationFormMeta } from 'components/Pages/Registration/formMeta';
import { useCountriesAsSelectOptions } from 'connectors/country/Country';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC, useEffect } from 'react';
import { Controller, useFormContext } from 'react-hook-form';
import { RegistrationFormType } from 'types/form';

export const Address: FC = () => {
    const t = useTypedTranslationFunction();
    const formProviderMethods = useFormContext<RegistrationFormType>();
    const { setValue } = formProviderMethods;
    const formMeta = useRegistrationFormMeta(formProviderMethods);
    const countrySelectOptions = useCountriesAsSelectOptions();

    useEffect(() => {
        if (countrySelectOptions.length > 0) {
            setValue(formMeta.fields.country.name, countrySelectOptions[0]);
        }
    }, [countrySelectOptions, formMeta.fields.country.name, setValue]);

    if (countrySelectOptions.length === 0) {
        return null;
    }

    return (
        <>
            <Heading type="h3">{t('Billing address')}</Heading>
            <TextInputControlled
                control={formProviderMethods.control}
                name={formMeta.fields.street.name}
                render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                formName={formMeta.formName}
                textInputProps={{
                    label: formMeta.fields.street.label,
                    required: true,
                    type: 'text',
                }}
            />
            <FormColumn>
                <TextInputControlled
                    control={formProviderMethods.control}
                    name={formMeta.fields.city.name}
                    render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                    formName={formMeta.formName}
                    textInputProps={{
                        label: formMeta.fields.city.label,
                        required: true,
                        type: 'text',
                    }}
                />
                <TextInputControlled
                    control={formProviderMethods.control}
                    name={formMeta.fields.postcode.name}
                    render={(textInput) => (
                        <FormLine bottomGap width="100%" lg="142px">
                            {textInput}
                        </FormLine>
                    )}
                    formName={formMeta.formName}
                    textInputProps={{
                        label: formMeta.fields.postcode.label,
                        required: true,
                        type: 'text',
                    }}
                />
            </FormColumn>
            <FormLine>
                <Controller
                    name={formMeta.fields.country.name}
                    render={({ fieldState: { invalid, error }, field }) => (
                        <>
                            <Select
                                label={formMeta.fields.country.label}
                                options={countrySelectOptions}
                                onChange={field.onChange}
                                value={countrySelectOptions.find((option) => option.value === field.value.value)}
                                hasError={invalid}
                            />
                            <FormLineError
                                error={error}
                                inputType="select"
                                testIdentifier={formMeta.formName + '-' + formMeta.fields.country.name + '-error'}
                            />
                        </>
                    )}
                />
            </FormLine>
        </>
    );
};
