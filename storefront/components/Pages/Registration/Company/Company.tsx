import { Heading } from 'components/Basic/Heading/Heading';
import { FormLine } from 'components/Forms/Lib/FormLine/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError/FormLineError';
import { TextInput } from 'components/Forms/TextInput/TextInput';
import { useRegistrationFormMeta } from 'components/Pages/Registration/formMeta';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';
import { Controller, useFormContext } from 'react-hook-form';
import { RegistrationFormType } from 'types/form';

export const Company: FC = () => {
    const t = useTypedTranslationFunction();
    const formProviderMethods = useFormContext<RegistrationFormType>();
    const formMeta = useRegistrationFormMeta(formProviderMethods);

    return (
        <>
            <Heading type="h3">{t('Company data')}</Heading>
            <FormLine bottomGap>
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

            <FormLine bottomGap>
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

            <FormLine>
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
