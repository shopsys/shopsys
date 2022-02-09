import { Controller, useFormContext, useWatch } from 'react-hook-form';
import { FC, useEffect } from 'react';
import { contactInformationActions } from 'redux/slices/contactInformation';
import { ContactInformationFormType } from 'types/form';
import FormColumn from 'components/Forms/Lib/FormColumn';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import { getCountriesAsSelectOptions } from 'connectors/country/Country';
import Heading from 'components/Basic/Heading';
import Select from 'components/Forms/Select';
import TextInput from 'components/Forms/TextInput';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/formMeta';
import { useShopsysDispatch } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const ContactInformationAddress: FC = () => {
    const dispatch = useShopsysDispatch();
    const t = useTypedTranslationFunction();
    const formProviderMethods = useFormContext<ContactInformationFormType>();
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const countrySelectOptions = getCountriesAsSelectOptions();
    const [streetValue, cityValue, postcodeValue] = useWatch({
        name: [formMeta.fields.street.name, formMeta.fields.city.name, formMeta.fields.postcode.name],
        control: formProviderMethods.control,
    });

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
                            <FormLineError error={error} inputType="text-input" />
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
                                    onBlurCapture={() => dispatch(contactInformationActions.setPostcode(postcodeValue))}
                                />
                                <FormLineError error={error} inputType="text-input" />
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
                            <FormLineError error={error} inputType="select" />
                        </>
                    )}
                />
            </FormLine>
        </>
    );
};

/* @component */
export default ContactInformationAddress;
