import { Controller, useFormContext } from 'react-hook-form';
import { RegistrationFormType, useRegistrationFormMeta } from 'components/Pages/Registration/formMeta';
import { FC } from 'react';
import FormColumn from 'components/Forms/Lib/FormColumn';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import Heading from 'components/Basic/Heading';
import TextInput from 'components/Forms/TextInput';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const Password: FC = () => {
    const t = useTypedTranslationFunction();
    const formProviderMethods = useFormContext<RegistrationFormType>();
    const formMeta = useRegistrationFormMeta(formProviderMethods);

    return (
        <>
            <Heading type="h3">{t('Create a password')}</Heading>
            <FormColumn>
                <FormLine bottomGap={true} width="100%" lg="50%">
                    <Controller
                        name={formMeta.fields.passwordFirst.name}
                        render={({ fieldState: { isTouched, invalid, error }, field }) => (
                            <>
                                <TextInput
                                    id={formMeta.formName + '-' + formMeta.fields.passwordFirst.name}
                                    name={formMeta.fields.passwordFirst.name}
                                    label={formMeta.fields.passwordFirst.label}
                                    required={true}
                                    type="password"
                                    isTouched={isTouched}
                                    hasError={invalid}
                                    fieldRef={field}
                                />
                                <FormLineError
                                    error={error}
                                    inputType="text-input-password"
                                    data-testid={
                                        formMeta.formName + '-' + formMeta.fields.passwordFirst.name + '-error'
                                    }
                                />
                            </>
                        )}
                    />
                </FormLine>
                <FormLine bottomGap={true} width="100%" lg="50%">
                    <Controller
                        name={formMeta.fields.passwordSecond.name}
                        render={({ fieldState: { isTouched, invalid, error }, field }) => (
                            <>
                                <TextInput
                                    id={formMeta.formName + '-' + formMeta.fields.passwordSecond.name}
                                    name={formMeta.fields.passwordSecond.name}
                                    label={formMeta.fields.passwordSecond.label}
                                    required={true}
                                    type="password"
                                    isTouched={isTouched}
                                    hasError={invalid}
                                    fieldRef={field}
                                />
                                <FormLineError
                                    error={error}
                                    inputType="text-input-password"
                                    data-testid={
                                        formMeta.formName + '-' + formMeta.fields.passwordSecond.name + '-error'
                                    }
                                />
                            </>
                        )}
                    />
                </FormLine>
            </FormColumn>
        </>
    );
};

/* @component */
export default Password;
