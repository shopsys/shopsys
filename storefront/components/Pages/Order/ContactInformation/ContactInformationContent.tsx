import { ContactInformationFormWrapper } from './ContactInformationFormWrapper/ContactInformationFormWrapper';
import { useContactInformationFormMeta } from './formMeta';
import { Link } from 'components/Basic/Link/Link';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine/ChoiceFormLine';
import { FormLine } from 'components/Forms/Lib/FormLine/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { usePrivacyPolicyArticleUrlQueryApi, useTermsAndConditionsArticleUrlQueryApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import Trans from 'next-translate/Trans';
import { useEffect, useState } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import { useShopsysDispatch } from 'redux/main';
import { contactInformationActions } from 'redux/slices/contactInformation';
import { twJoin } from 'tailwind-merge';
import { ContactInformationFormType } from 'types/form';

export const ContactInformationContent: FC = () => {
    const dispatch = useShopsysDispatch();
    const formProviderMethods = useFormContext<ContactInformationFormType>();
    const { trigger, formState } = formProviderMethods;
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const emailValue = useWatch({ name: formMeta.fields.email.name, control: formProviderMethods.control });
    const [isEmailFilledCorrectly, setIsEmailFilledCorrectly] = useState(false);
    const [{ data: termsAndConditionsArticleUrlData }] = useQueryError(useTermsAndConditionsArticleUrlQueryApi());
    const termsAndConditionsArticleUrl = termsAndConditionsArticleUrlData?.termsAndConditionsArticle?.slug;
    const [{ data: privacyPolicyArticleUrlData }] = useQueryError(usePrivacyPolicyArticleUrlQueryApi());
    const privacyPolicyArticleUrl = privacyPolicyArticleUrlData?.privacyPolicyArticle?.slug;

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
                    type: 'email',
                    autoComplete: 'email',
                    onBlur: () => dispatch(contactInformationActions.setEmail(emailValue)),
                }}
            />
            <ContactInformationFormWrapper isEmailEntered={isEmailFilledCorrectly} />
            <div className={twJoin(!isEmailFilledCorrectly && 'pointer-events-none opacity-50')}>
                <p className="mb-4">
                    <Trans
                        i18nKey="ContactInformationInfo"
                        defaultTrans="By clicking on the Send order button, you agree with <lnk1>terms and conditions</lnk1> of the e-shop and with the <lnk2>processing of privacy policy</lnk2>."
                        components={{
                            lnk1:
                                termsAndConditionsArticleUrl !== undefined ? (
                                    <Link href={termsAndConditionsArticleUrl} linkType="external" target="_blank" />
                                ) : (
                                    <span></span>
                                ),
                            lnk2:
                                privacyPolicyArticleUrl !== undefined ? (
                                    <Link href={privacyPolicyArticleUrl} linkType="external" target="_blank" />
                                ) : (
                                    <span></span>
                                ),
                        }}
                    />
                </p>
                <CheckboxControlled
                    name={formMeta.fields.newsletterSubscription.name}
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    render={(checkbox) => <ChoiceFormLine>{checkbox}</ChoiceFormLine>}
                    checkboxProps={{
                        label: formMeta.fields.newsletterSubscription.label,
                    }}
                />
            </div>
        </>
    );
};
