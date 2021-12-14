import { Controller, useFormContext, useWatch } from 'react-hook-form';
import { contactInformationActions } from 'redux/slices/contactInformation';
import { ContactInformationFormType } from 'types/form';
import { FC } from 'react';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import Heading from 'components/Basic/Heading';
import TextInput from 'components/Forms/TextInput';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/formMeta';
import { useShopsysDispatch } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const ContactInformationCompany: FC = () => {
    const dispatch = useShopsysDispatch();
    const t = useTypedTranslationFunction();
    const formProviderMethods = useFormContext<ContactInformationFormType>();
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const [companyNameValue, companyNumberValue, companyTaxNumberValue] = useWatch({
        name: [
            formMeta.fields.companyName.name,
            formMeta.fields.companyNumber.name,
            formMeta.fields.companyTaxNumber.name,
        ],
        control: formProviderMethods.control,
    });

    return (
        <>
            <Heading type="h3">{t('Company data')}</Heading>
            <FormLine bottomGap={true} lg="65%">
                <Controller
                    name={formMeta.fields.companyName.name}
                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                        <>
                            <TextInput
                                id={formMeta.formName + '-' + formMeta.fields.companyName.name}
                                name={formMeta.fields.companyName.name}
                                label={formMeta.fields.companyName.label}
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
                    name={formMeta.fields.companyNumber.name}
                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                        <>
                            <TextInput
                                id={formMeta.formName + '-' + formMeta.fields.companyNumber.name}
                                name={formMeta.fields.companyNumber.name}
                                label={formMeta.fields.companyNumber.label}
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
                    name={formMeta.fields.companyTaxNumber.name}
                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                        <>
                            <TextInput
                                id={formMeta.formName + '-' + formMeta.fields.companyTaxNumber.name}
                                name={formMeta.fields.companyTaxNumber.name}
                                label={formMeta.fields.companyTaxNumber.label}
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
