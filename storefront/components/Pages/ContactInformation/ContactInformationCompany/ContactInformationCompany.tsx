import { Controller } from 'react-hook-form';
import { FC } from 'react';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import Heading from 'components/Basic/Heading';
import TextInput from 'components/Forms/TextInput';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const ContactInformationCompany: FC = () => {
    const t = useTypedTranslationFunction();

    return (
        <>
            <Heading type="h3">{t('Company data')}</Heading>
            <FormLine bottomGap={true} Lg="65%">
                <Controller
                    name="companyName"
                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                        <>
                            <TextInput
                                id="contactInformation_form-companyName"
                                name="companyName"
                                label={t('Company name')}
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

            <FormLine bottomGap={true} Lg="65%">
                <Controller
                    name="companyNumber"
                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                        <>
                            <TextInput
                                id="contactInformation_form-companyNumber"
                                name="companyNumber"
                                label={t('Company number')}
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

            <FormLine bottomGap={true} Lg="65%">
                <Controller
                    name="companyTaxNumber"
                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                        <>
                            <TextInput
                                id="contactInformation_form-companyTaxNumber"
                                name="companyTaxNumber"
                                label={t('Tax number')}
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
        </>
    );
};

/* @component */
export default ContactInformationCompany;
