import { Controller, useFormContext } from 'react-hook-form';
import { RegistrationFormType, useRegistrationFormMeta } from 'components/Pages/Registration/formMeta';
import { FC } from 'react';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import Heading from 'components/Basic/Heading';
import TextInput from 'components/Forms/TextInput';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const Company: FC = () => {
    const t = useTypedTranslationFunction();
    const formProviderMethods = useFormContext<RegistrationFormType>();
    const formMeta = useRegistrationFormMeta(formProviderMethods);

    return (
        <>
            <Heading type="h3">{t('Company data')}</Heading>
            <FormLine bottomGap={true}>
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
                            />
                            <FormLineError
                                error={error}
                                inputType="text-input"
                                data-testid={formMeta.formName + '-' + formMeta.fields.companyName.name + '-error'}
                            />
                        </>
                    )}
                />
            </FormLine>

            <FormLine bottomGap={true}>
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
                            />
                            <FormLineError
                                error={error}
                                inputType="text-input"
                                data-testid={formMeta.formName + '-' + formMeta.fields.companyNumber.name + '-error'}
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
                                data-testid={formMeta.formName + '-' + formMeta.fields.companyTaxNumber.name + '-error'}
                            />
                        </>
                    )}
                />
            </FormLine>
        </>
    );
};

/* @component */
export default Company;
