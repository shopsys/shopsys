import { Controller, useFormContext } from 'react-hook-form';
import { FC, useEffect } from 'react';
import { RegistrationFormType, useRegistrationFormMeta } from 'components/Pages/Registration/formMeta';
import FormColumn from 'components/Forms/Lib/FormColumn';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import { getCountriesAsSelectOptions } from 'connectors/country/Country';
import Heading from 'components/Basic/Heading';
import Select from 'components/Forms/Select';
import TextInput from 'components/Forms/TextInput';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const Address: FC = () => {
    const t = useTypedTranslationFunction();
    const formProviderMethods = useFormContext<RegistrationFormType>();
    const formMeta = useRegistrationFormMeta(formProviderMethods);
    const countrySelectOptions = getCountriesAsSelectOptions();

    useEffect(() => {
        if (countrySelectOptions.length > 0) {
            formProviderMethods.setValue(formMeta.fields.country.name, countrySelectOptions[0]);
        }
    }, [JSON.stringify(countrySelectOptions)]);

    if (countrySelectOptions.length === 0) {
        return null;
    }

    return (
        <>
            <Heading type="h3">{t('Billing address')}</Heading>
            <FormLine bottomGap={true}>
                <Controller
                    name={formMeta.fields.street.name}
                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                        <>
                            <TextInput
                                id={formMeta.formName + '-' + formMeta.fields.street.name}
                                name={formMeta.fields.street.name}
                                label={formMeta.fields.street.label}
                                required={true}
                                type="text"
                                isTouched={isTouched}
                                hasError={invalid}
                                fieldRef={field}
                            />
                            <FormLineError error={error} inputType="text-input" />
                        </>
                    )}
                />
            </FormLine>

            <FormColumn>
                <FormLine bottomGap={true}>
                    <Controller
                        name={formMeta.fields.city.name}
                        render={({ fieldState: { isTouched, invalid, error }, field }) => (
                            <>
                                <TextInput
                                    id={formMeta.formName + '-' + formMeta.fields.city.name}
                                    name={formMeta.fields.city.name}
                                    label={formMeta.fields.city.label}
                                    required={true}
                                    type="text"
                                    isTouched={isTouched}
                                    hasError={invalid}
                                    fieldRef={field}
                                />
                                <FormLineError error={error} inputType="text-input" />
                            </>
                        )}
                    />
                </FormLine>
                <FormLine bottomGap={true} width="100%" lg="142px">
                    <Controller
                        name={formMeta.fields.postcode.name}
                        render={({ fieldState: { isTouched, invalid, error }, field }) => (
                            <>
                                <TextInput
                                    id={formMeta.formName + '-' + formMeta.fields.postcode.name}
                                    name={formMeta.fields.postcode.name}
                                    label={formMeta.fields.postcode.label}
                                    required={true}
                                    type="text"
                                    isTouched={isTouched}
                                    hasError={invalid}
                                    fieldRef={field}
                                />
                                <FormLineError error={error} inputType="text-input" />
                            </>
                        )}
                    />
                </FormLine>
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
                                fieldRef={field}
                            />
                            <FormLineError error={error} inputType="select" />
                        </>
                    )}
                />
            </FormLine>
        </>
    );
};

/* @component */
export default Address;
