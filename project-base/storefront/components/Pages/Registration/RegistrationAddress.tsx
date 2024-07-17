import { FormHeading, FormBlockWrapper } from 'components/Forms/Form/Form';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { Select } from 'components/Forms/Select/Select';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useRegistrationFormMeta } from 'components/Pages/Registration/registrationFormMeta';
import useTranslation from 'next-translate/useTranslation';
import { useEffect } from 'react';
import { Controller, useFormContext } from 'react-hook-form';
import { RegistrationFormType } from 'types/form';
import { useCountriesAsSelectOptions } from 'utils/countries/useCountriesAsSelectOptions';

export const RegistrationAddress: FC = () => {
    const { t } = useTranslation();
    const formProviderMethods = useFormContext<RegistrationFormType>();
    const { setValue } = formProviderMethods;
    const formMeta = useRegistrationFormMeta(formProviderMethods);
    const countriesAsSelectOptions = useCountriesAsSelectOptions();

    useEffect(() => {
        if (countriesAsSelectOptions.length > 0) {
            setValue(formMeta.fields.country.name, countriesAsSelectOptions[0], { shouldValidate: true });
        }
    }, [countriesAsSelectOptions, formMeta.fields.country.name, setValue]);

    if (countriesAsSelectOptions.length === 0) {
        return null;
    }

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
                    }}
                />
            </FormColumn>
            <FormLine>
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
                            <FormLineError error={error} inputType="select" />
                        </>
                    )}
                />
            </FormLine>
        </FormBlockWrapper>
    );
};
