import { Link, linkPlaceholderTwClass } from 'components/Basic/Link/Link';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/contactInformationFormMeta';
import { usePrivacyPolicyArticleUrlQuery } from 'graphql/requests/articles/queries/PrivacyPolicyArticleUrlQuery.generated';
import { useTermsAndConditionsArticleUrlQuery } from 'graphql/requests/articles/queries/TermsAndConditionsArticleUrlQuery.generated';
import Trans from 'next-translate/Trans';
import { useFormContext, useWatch } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';
import { twJoin } from 'tailwind-merge';

export const ContactInformationSendOrderButton: FC = () => {
    const formProviderMethods = useFormContext<ContactInformation>();
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);

    const { formState } = formProviderMethods;

    const formMeta = useContactInformationFormMeta(formProviderMethods);

    const emailValue = useWatch({ name: formMeta.fields.email.name, control: formProviderMethods.control });
    const [{ data: termsAndConditionsArticleUrlData }] = useTermsAndConditionsArticleUrlQuery();
    const [{ data: privacyPolicyArticleUrlData }] = usePrivacyPolicyArticleUrlQuery();
    const termsAndConditionsArticleUrl = termsAndConditionsArticleUrlData?.termsAndConditionsArticle?.slug;
    const privacyPolicyArticleUrl = privacyPolicyArticleUrlData?.privacyPolicyArticle?.slug;

    const isEmailFilledCorrectly = !!emailValue && !formState.errors.email;

    return (
        <div className={twJoin(!isEmailFilledCorrectly && 'pointer-events-none opacity-50')}>
            <p className="mb-4">
                <Trans
                    defaultTrans="By clicking on the Send order button, you agree with <lnk1>terms and conditions</lnk1> of the e-shop and with the <lnk2>processing of privacy policy</lnk2>."
                    i18nKey="ContactInformationInfo"
                    components={{
                        lnk1:
                            termsAndConditionsArticleUrl !== undefined ? (
                                <Link isExternal href={termsAndConditionsArticleUrl} target="_blank" />
                            ) : (
                                <span className={linkPlaceholderTwClass} />
                            ),
                        lnk2:
                            privacyPolicyArticleUrl !== undefined ? (
                                <Link isExternal href={privacyPolicyArticleUrl} target="_blank" />
                            ) : (
                                <span className={linkPlaceholderTwClass} />
                            ),
                    }}
                />
            </p>

            <CheckboxControlled
                control={formProviderMethods.control}
                formName={formMeta.formName}
                name={formMeta.fields.newsletterSubscription.name}
                render={(checkbox) => <ChoiceFormLine>{checkbox}</ChoiceFormLine>}
                checkboxProps={{
                    label: formMeta.fields.newsletterSubscription.label,
                }}
                onChange={(event) =>
                    updateContactInformation({ newsletterSubscription: Boolean(event.currentTarget.value) })
                }
            />
        </div>
    );
};
