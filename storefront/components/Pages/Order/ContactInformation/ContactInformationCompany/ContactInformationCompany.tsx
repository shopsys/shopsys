import { Controller, useWatch } from 'react-hook-form';
import { contactInformationActions } from 'redux/slices/contactInformation';
import { FC } from 'react';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import Heading from 'components/Basic/Heading';
import TextInput from 'components/Forms/TextInput';
import { useShopsysDispatch } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const ContactInformationCompany: FC = () => {
    const dispatch = useShopsysDispatch();
    const t = useTypedTranslationFunction();
    const companyNameValue = useWatch({ name: 'companyName' });
    const companyNumberValue = useWatch({ name: 'companyNumber' });
    const companyTaxNumberValue = useWatch({ name: 'companyTaxNumber' });

    return (
        <>
            <Heading type="h3">{t('Company data')}</Heading>
            <FormLine bottomGap={true} lg="65%">
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
                                onBlurCapture={() =>
                                    dispatch(contactInformationActions.setCompanyName(companyNameValue))
                                }
                            />
                            <FormLineError error={error} inputType="text-input" />
                        </>
                    )}
                />
            </FormLine>

            <FormLine bottomGap={true} lg="65%">
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
                                onBlurCapture={() =>
                                    dispatch(contactInformationActions.setCompanyNumber(companyNumberValue))
                                }
                            />
                            <FormLineError error={error} inputType="text-input" />
                        </>
                    )}
                />
            </FormLine>

            <FormLine bottomGap={true} lg="65%">
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
                                onBlurCapture={() =>
                                    dispatch(contactInformationActions.setCompanyTaxNumber(companyTaxNumberValue))
                                }
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
