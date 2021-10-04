import { FC, useState } from 'react';
import ChoiceFormLine from 'components/Forms/Lib/ChoiceFormLine';
import { Controller } from 'react-hook-form';
import FormColumn from 'components/Forms/Lib/FormColumn';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import Heading from 'components/Basic/Heading';
import RegisterCheckbox from './RegisterCheckbox';
import TextInput from 'components/Forms/TextInput';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const ContactInformationRegister: FC = () => {
    const t = useTypedTranslationFunction();
    const [isRegisterChecked, setIsRegisterChecked] = useState(false);

    return (
        <>
            <ChoiceFormLine>
                <Controller
                    name="register"
                    render={({ field }) => (
                        <RegisterCheckbox field={field} setIsRegisterChecked={setIsRegisterChecked} />
                    )}
                />
            </ChoiceFormLine>
            {isRegisterChecked && (
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
                </>
            )}
        </>
    );
};

/* @component */
export default ContactInformationRegister;
