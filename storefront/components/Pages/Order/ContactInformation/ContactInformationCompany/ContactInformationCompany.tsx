import { Heading } from 'components/Basic/Heading/Heading';
import { FormLine } from 'components/Forms/Lib/FormLine/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError/FormLineError';
import { TextInput } from 'components/Forms/TextInput/TextInput';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/formMeta';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC } from 'react';
import { Controller, useFormContext, useWatch } from 'react-hook-form';
import { useShopsysDispatch } from 'redux/main';
import { contactInformationActions } from 'redux/slices/contactInformation';
import { ContactInformationFormType } from 'types/form';

export const ContactInformationCompany: FC = () => {
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
            <FormLine bottomGap lg="65%">
                <Controller
                    name={formMeta.fields.companyName.name}
                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                        <>
                            <TextInput
                                id={formMeta.formName + '-' + formMeta.fields.companyName.name}
                                name={formMeta.fields.companyName.name}
                                label={formMeta.fields.companyName.label}
                                required
                                type="text"
                                isTouched={isTouched}
                                hasError={invalid}
                                fieldRef={field}
                                onBlurCapture={() =>
                                    dispatch(contactInformationActions.setCompanyName(companyNameValue))
                                }
                            />
                            <FormLineError
                                error={error}
                                inputType="text-input"
                                testIdentifier={formMeta.formName + '-' + formMeta.fields.companyName.name + '-error'}
                            />
                        </>
                    )}
                />
            </FormLine>

            <FormLine bottomGap lg="65%">
                <Controller
                    name={formMeta.fields.companyNumber.name}
                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                        <>
                            <TextInput
                                id={formMeta.formName + '-' + formMeta.fields.companyNumber.name}
                                name={formMeta.fields.companyNumber.name}
                                label={formMeta.fields.companyNumber.label}
                                required
                                type="text"
                                isTouched={isTouched}
                                hasError={invalid}
                                fieldRef={field}
                                onBlurCapture={() =>
                                    dispatch(contactInformationActions.setCompanyNumber(companyNumberValue))
                                }
                            />
                            <FormLineError
                                error={error}
                                inputType="text-input"
                                testIdentifier={formMeta.formName + '-' + formMeta.fields.companyNumber.name + '-error'}
                            />
                        </>
                    )}
                />
            </FormLine>

            <FormLine bottomGap lg="65%">
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
                            <FormLineError
                                error={error}
                                inputType="text-input"
                                testIdentifier={
                                    formMeta.formName + '-' + formMeta.fields.companyTaxNumber.name + '-error'
                                }
                            />
                        </>
                    )}
                />
            </FormLine>
        </>
    );
};
