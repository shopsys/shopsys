import { ContactInformationTextStyled, ContactInformationTextWrapperStyled } from './ContactInformation.style';
import { Controller, useFormContext, useWatch } from 'react-hook-form';
import { FC, useEffect, useState } from 'react';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import Checkbox from 'components/Forms/Checkbox';
import ChoiceFormLine from 'components/Forms/Lib/ChoiceFormLine';
import { contactInformationActions } from 'redux/slices/contactInformation';
import ContactInformationContent from './ContactInformationContent';
import { ContactInformationFormType } from 'types/form';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';
import Link from 'components/Basic/Link';
import TextInput from 'components/Forms/TextInput';
import Trans from 'next-translate/Trans';
import { useContactInformationFormMeta } from './formMeta';

const ContactInformation: FC = () => {
    const dispatch = useShopsysDispatch();
    const formProviderMethods = useFormContext<ContactInformationFormType>();
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const emailValue = useWatch({ name: formMeta.fields.email.name, control: formProviderMethods.control });
    const [isEmailFilledCorrectly, setIsEmailFilledCorrectly] = useState(false);
    const { url } = useShopsysSelector((state) => state.domain);
    const [TermsAndConditionUrl, GdprUrl] = getInternationalizedStaticUrls(['/terms-and-conditions', '/gdpr'], url);
    const { isUserLoggedIn } = useShopsysSelector((state) => state.user);

    useEffect(() => {
        if (formProviderMethods.formState.touchedFields.email !== undefined) {
            setIsEmailFilledCorrectly(formProviderMethods.formState.errors.email === undefined);
            return;
        }

        if (emailValue.length > 0) {
            formProviderMethods.trigger('email', { shouldFocus: true }).then((isEmailValid) => {
                setIsEmailFilledCorrectly(isEmailValid);
            });
        }
    }, [emailValue]);

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
                                required={true}
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
                            lnk1: <Link href={TermsAndConditionUrl} linkType="external" target="_blank" />,
                            lnk2: <Link href={GdprUrl} linkType="external" target="_blank" />,
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
