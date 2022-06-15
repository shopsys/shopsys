import { ContactInformationTextStyled, ContactInformationTextWrapperStyled } from './ContactInformation.style';
import ContactInformationContent from './ContactInformationContent';
import { useContactInformationFormMeta } from './formMeta';
import Link from 'components/Basic/Link';
import Checkbox from 'components/Forms/Checkbox';
import ChoiceFormLine from 'components/Forms/Lib/ChoiceFormLine';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import TextInput from 'components/Forms/TextInput';
import { useGetPrivacyPolicyUrl } from 'hooks/routes/useGetPrivacyPolicyUrl';
import { useGetTermsAndConditionsUrl } from 'hooks/routes/useGetTermsAndConditionsUrl';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import Trans from 'next-translate/Trans';
import { FC, useEffect, useState } from 'react';
import { Controller, useFormContext, useWatch } from 'react-hook-form';
import { useShopsysDispatch } from 'redux/main';
import { contactInformationActions } from 'redux/slices/contactInformation';
import { ContactInformationFormType } from 'types/form';

const ContactInformation: FC = () => {
    const dispatch = useShopsysDispatch();
    const formProviderMethods = useFormContext<ContactInformationFormType>();
    const { trigger, formState } = formProviderMethods;
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const emailValue = useWatch({ name: formMeta.fields.email.name, control: formProviderMethods.control });
    const [isEmailFilledCorrectly, setIsEmailFilledCorrectly] = useState(false);
    const termsAndConditionUrl = useGetTermsAndConditionsUrl();
    const gdprUrl = useGetPrivacyPolicyUrl();
    const { isUserLoggedIn } = useCurrentUserData();

    useEffect(() => {
        if (formState.touchedFields.email !== undefined) {
            setIsEmailFilledCorrectly(formState.errors.email === undefined);
            return;
        }

        if (emailValue.length > 0) {
            trigger('email', { shouldFocus: true }).then((isEmailValid) => {
                setIsEmailFilledCorrectly(isEmailValid);
            });
        }
    }, [emailValue, trigger, formState.touchedFields, formState.errors]);

    return (
        <>
            <FormLine bottomGap={true} lg="65%">
                <Controller
                    name={formMeta.fields.email.name}
                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                        <>
                            <TextInput
                                id={formMeta.formName + '-' + formMeta.fields.email.name}
                                name={formMeta.fields.email.name}
                                label={formMeta.fields.email.label}
                                disabled={isUserLoggedIn}
                                required
                                type="text"
                                isTouched={isTouched}
                                hasError={invalid}
                                fieldRef={field}
                                onBlurCapture={() => dispatch(contactInformationActions.setEmail(emailValue))}
                            />
                            <FormLineError
                                error={error}
                                inputType="text-input"
                                data-testid={formMeta.formName + '-' + formMeta.fields.email.name + '-error'}
                            />
                        </>
                    )}
                />
            </FormLine>
            <ContactInformationContent isEmailEntered={isEmailFilledCorrectly} />
            <ContactInformationTextWrapperStyled isEmailEntered={isEmailFilledCorrectly}>
                <ContactInformationTextStyled>
                    <Trans
                        i18nKey="ContactInformationInfo"
                        defaultTrans="By clicking on the Send order button, you agree with <lnk1>terms and conditions</lnk1> of the e-shop and with the <lnk2>processing of privacy policy</lnk2>."
                        components={{
                            lnk1: <Link href={termsAndConditionUrl} linkType="external" target="_blank" />,
                            lnk2: <Link href={gdprUrl} linkType="external" target="_blank" />,
                        }}
                    />
                </ContactInformationTextStyled>
                <ChoiceFormLine>
                    <Controller
                        name={formMeta.fields.newsletterSubscription.name}
                        render={({ field }) => (
                            <Checkbox
                                id={formMeta.formName + '-' + formMeta.fields.newsletterSubscription.name}
                                name={formMeta.fields.newsletterSubscription.name}
                                label={formMeta.fields.newsletterSubscription.label}
                                fieldRef={field}
                            />
                        )}
                    />
                </ChoiceFormLine>
            </ContactInformationTextWrapperStyled>
        </>
    );
};

/* @component */
export default ContactInformation;
