import {
    ContactInformationFormType,
    useContactInformationFormMeta,
} from 'components/Pages/Order/ContactInformation/formMeta';
import { Controller, useFormContext, useWatch } from 'react-hook-form';
import Checkbox from 'components/Forms/Checkbox';
import ChoiceFormLine from 'components/Forms/Lib/ChoiceFormLine';
import { contactInformationActions } from 'redux/slices/contactInformation';
import { FC } from 'react';
import FormColumn from 'components/Forms/Lib/FormColumn';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import Heading from 'components/Basic/Heading';
import TextInput from 'components/Forms/TextInput';
import { useShopsysDispatch } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const ContactInformationRegister: FC = () => {
    const dispatch = useShopsysDispatch();
    const t = useTypedTranslationFunction();
    const formProviderMethods = useFormContext<ContactInformationFormType>();
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const [registerValue, passwordFirstValue, passwordSecondValue] = useWatch({
        name: [formMeta.fields.register.name, formMeta.fields.passwordFirst.name, formMeta.fields.passwordSecond.name],
        control: formProviderMethods.control,
    });

    return (
        <>
            <ChoiceFormLine>
                <Controller
                    name={formMeta.fields.register.name}
                    render={({ field }) => (
                        <Checkbox
                            id={formMeta.formName + '-' + formMeta.fields.register.name}
                            name={formMeta.fields.register.name}
                            fieldRef={field}
                            label={formMeta.fields.register.label}
                        />
                    )}
                />
            </ChoiceFormLine>
            {registerValue === true && (
                <>
                    <Heading type="h3">{t('Create a password')}</Heading>
                    <FormColumn lg="65%">
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
                                            onBlurCapture={() =>
                                                dispatch(contactInformationActions.setPasswordFirst(passwordFirstValue))
                                            }
                                        />
                                        <FormLineError error={error} inputType="text-input-password" />
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
                                            onBlurCapture={() =>
                                                dispatch(
                                                    contactInformationActions.setPasswordSecond(passwordSecondValue),
                                                )
                                            }
                                        />
                                        <FormLineError error={error} inputType="text-input-password" />
                                    </>
                                )}
                            />
                        </FormLine>
                    </FormColumn>
                </>
            )}
        </>
    );
};

/* @component */
export default ContactInformationRegister;
