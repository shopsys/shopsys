import { Heading } from 'components/Basic/Heading/Heading';
import { FormColumn } from 'components/Forms/Lib/FormColumn/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError/FormLineError';
import { Select } from 'components/Forms/Select/Select';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/formMeta';
import { useCountriesAsSelectOptions } from 'connectors/country/Country';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC, useEffect } from 'react';
import { Controller, useFormContext, useWatch } from 'react-hook-form';
import { useShopsysDispatch } from 'redux/main';
import { contactInformationActions } from 'redux/slices/contactInformation';
import { ContactInformationFormType } from 'types/form';

export const ContactInformationAddress: FC = () => {
    const dispatch = useShopsysDispatch();
    const t = useTypedTranslationFunction();
    const formProviderMethods = useFormContext<ContactInformationFormType>();
    const { setValue } = formProviderMethods;
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const countrySelectOptions = useCountriesAsSelectOptions();
    const [countryValue] = useWatch({
        name: [formMeta.fields.country.name],
        control: formProviderMethods.control,
    });

    useEffect(() => {
        if (countrySelectOptions.length > 0 && countryValue.value === '') {
            setValue(formMeta.fields.country.name, countrySelectOptions[0]);
        }
    }, [countrySelectOptions, countryValue, formMeta.fields.country.name, setValue]);

    if (countrySelectOptions.length === 0) {
        return null;
    }

    return (
        <>
            <Heading type="h3">{t('Billing address')}</Heading>
            <FormLine bottomGap lg="65%">
                <TextInputControlled
                    control={formProviderMethods.control}
                    name={formMeta.fields.street.name}
                    render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                    formName={formMeta.formName}
                    textInputProps={{
                        label: formMeta.fields.street.label,
                        required: true,
                        type: 'text',
                        onBlur: (event) => dispatch(contactInformationActions.setStreet(event.currentTarget.value)),
                    }}
                />
            </FormLine>
            <FormColumn lg="65%">
                <TextInputControlled
                    control={formProviderMethods.control}
                    name={formMeta.fields.city.name}
                    render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                    formName={formMeta.formName}
                    textInputProps={{
                        label: formMeta.fields.city.label,
                        required: true,
                        type: 'text',
                        onBlur: (event) => dispatch(contactInformationActions.setCity(event.currentTarget.value)),
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
                        onBlur: (event) => dispatch(contactInformationActions.setPostcode(event.currentTarget.value)),
                    }}
                />
            </FormColumn>
            <FormLine lg="65%">
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
