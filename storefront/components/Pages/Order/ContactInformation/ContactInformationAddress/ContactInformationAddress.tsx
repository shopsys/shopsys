import { Controller, useFormContext } from 'react-hook-form';
import { FC, useEffect } from 'react';
import FormColumn from 'components/Forms/Lib/FormColumn';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import { getCountriesAsSelectOptions } from 'connectors/country/Country';
import Heading from 'components/Basic/Heading';
import Select from 'components/Forms/Select';
import TextInput from 'components/Forms/TextInput';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const ContactInformationAddress: FC = () => {
    const t = useTypedTranslationFunction();
    const formProviderMethods = useFormContext();
    const countrySelectOptions = getCountriesAsSelectOptions();
    useEffect(() => {
        if (countrySelectOptions.length > 0) {
            formProviderMethods.setValue('country', countrySelectOptions[0]);
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
                    name="street"
                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                        <>
                            <TextInput
                                id="contactInformation_form-street"
                                name="street"
                                label={t('Street')}
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

            <FormColumn lg="65%">
                <FormLine bottomGap={true}>
                    <Controller
                        name="city"
                        render={({ fieldState: { isTouched, invalid, error }, field }) => (
                            <>
                                <TextInput
                                    id="contactInformation_form-city"
                                    name="city"
                                    label={t('City')}
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
                        name="postcode"
                        render={({ fieldState: { isTouched, invalid, error }, field }) => (
                            <>
                                <TextInput
                                    id="contactInformation_form-postcode"
                                    name="postcode"
                                    label={t('Postcode')}
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
            <FormLine lg="65%">
                <Controller
                    name="country"
                    render={({ field }) => (
                        <Select
                            options={countrySelectOptions}
                            onChange={field.onChange}
                            value={countrySelectOptions.find((option) => option.value === field.value.value)}
                        />
                    )}
                />
            </FormLine>
        </>
    );
};

/* @component */
export default ContactInformationAddress;
