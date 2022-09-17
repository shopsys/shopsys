import { ContactInformationTextStyled, ContactInformationTextWrapperStyled } from './ContactInformationContent.style';
import { ContactInformationFormWrapper } from './ContactInformationFormWrapper/ContactInformationFormWrapper';
import { useContactInformationFormMeta } from './formMeta';
import { Link } from 'components/Basic/Link/Link';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine/ChoiceFormLine';
import { FormLine } from 'components/Forms/Lib/FormLine/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useGetPrivacyPolicyUrl } from 'hooks/routes/useGetPrivacyPolicyUrl';
import { useGetTermsAndConditionsUrl } from 'hooks/routes/useGetTermsAndConditionsUrl';
import Trans from 'next-translate/Trans';
import { FC, useEffect, useState } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import { useShopsysDispatch } from 'redux/main';
import { contactInformationActions } from 'redux/slices/contactInformation';
import { ContactInformationFormType } from 'types/form';

export const ContactInformationContent: FC = () => {
    const dispatch = useShopsysDispatch();
    const formProviderMethods = useFormContext<ContactInformationFormType>();
    const { trigger, formState } = formProviderMethods;
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const emailValue = useWatch({ name: formMeta.fields.email.name, control: formProviderMethods.control });
    const [isEmailFilledCorrectly, setIsEmailFilledCorrectly] = useState(false);
    const termsAndConditionUrl = useGetTermsAndConditionsUrl();
    const gdprUrl = useGetPrivacyPolicyUrl();

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
            <TextInputControlled
                control={formProviderMethods.control}
                name={formMeta.fields.email.name}
                render={(textInput) => (
                    <FormLine bottomGap lg="65%">
                        {textInput}
                    </FormLine>
                )}
                formName={formMeta.formName}
                textInputProps={{
                    label: formMeta.fields.email.label,
                    required: true,
                    type: 'text',
                    onBlur: () => dispatch(contactInformationActions.setEmail(emailValue)),
                }}
            />
            <ContactInformationFormWrapper isEmailEntered={isEmailFilledCorrectly} />
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
                <CheckboxControlled
                    name={formMeta.fields.newsletterSubscription.name}
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    render={(checkbox) => <ChoiceFormLine>{checkbox}</ChoiceFormLine>}
                    checkboxProps={{
                        label: formMeta.fields.newsletterSubscription.label,
                    }}
                />
            </ContactInformationTextWrapperStyled>
        </>
    );
};
