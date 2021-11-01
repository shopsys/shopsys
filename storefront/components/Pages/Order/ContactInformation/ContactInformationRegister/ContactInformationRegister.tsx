import { Controller, useWatch } from 'react-hook-form';
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
    const registerValue = useWatch({ name: 'register' });
    const passwordFirstValue = useWatch({ name: 'passwordFirst' });
    const passwordSecondValue = useWatch({ name: 'passwordSecond' });

    return (
        <>
            <ChoiceFormLine>
                <Controller
                    name="register"
                    render={({ field }) => (
                        <Checkbox
                            name={field.name}
                            fieldRef={field}
                            id="contactInformation_form-register"
                            label={t('I want to register with an order')}
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
                                name="passwordFirst"
                                render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                    <>
                                        <TextInput
                                            id="contactInformation_form-passwordFirst"
                                            name="passwordFirst"
                                            label={t('Password')}
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
                                name="passwordSecond"
                                render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                    <>
                                        <TextInput
                                            id="contactInformation_form-passwordSecond"
                                            name="passwordSecond"
                                            label={t('Password again')}
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
