import { Heading } from 'components/Basic/Heading/Heading';
import { FormColumn } from 'components/Forms/Lib/FormColumn/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError/FormLineError';
import { Select } from 'components/Forms/Select/Select';
import { TextInput } from 'components/Forms/TextInput/TextInput';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/formMeta';
import { useCountriesAsSelectOptions } from 'connectors/country/Country';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
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
    const [streetValue, cityValue, postcodeValue, countryValue] = useWatch({
        name: [
            formMeta.fields.street.name,
            formMeta.fields.city.name,
            formMeta.fields.postcode.name,
            formMeta.fields.country.name,
        ],
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
            <FormLine bottomGap={true} lg="65%">
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
                                onBlurCapture={() => dispatch(contactInformationActions.setStreet(streetValue))}
                            />
                            <FormLineError
                                error={error}
                                inputType="text-input"
                                data-testid={formMeta.formName + '-' + formMeta.fields.street.name + '-error'}
                            />
                        </>
                    )}
                />
            </FormLine>

            <FormColumn lg="65%">
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
                                    onBlurCapture={() => dispatch(contactInformationActions.setCity(cityValue))}
                                />
                                <FormLineError
                                    error={error}
                                    inputType="text-input"
                                    data-testid={formMeta.formName + '-' + formMeta.fields.city.name + '-error'}
                                />
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
                                    onBlurCapture={() => dispatch(contactInformationActions.setPostcode(postcodeValue))}
                                />
                                <FormLineError
                                    error={error}
                                    inputType="text-input"
                                    data-testid={formMeta.formName + '-' + formMeta.fields.postcode.name + '-error'}
                                />
                            </>
                        )}
                    />
                </FormLine>
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
                                fieldRef={field}
                            />
                            <FormLineError
                                error={error}
                                inputType="select"
                                data-testid={formMeta.formName + '-' + formMeta.fields.country.name + '-error'}
                            />
                        </>
                    )}
                />
            </FormLine>
        </>
    );
};
