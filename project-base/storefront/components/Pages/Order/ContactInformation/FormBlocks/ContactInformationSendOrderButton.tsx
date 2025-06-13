import { Link, linkPlaceholderTwClass } from 'components/Basic/Link/Link';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/contactInformationFormMeta';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import Trans from 'next-translate/Trans';
import useTranslation from 'next-translate/useTranslation';
import { useFormContext, useWatch } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';
import { twJoin } from 'tailwind-merge';

export const ContactInformationSendOrderButton: FC = () => {
    const { t } = useTranslation();
    const formProviderMethods = useFormContext<ContactInformation>();
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);
    const { canManagePersonalData } = useAuthorization();

    const { formState } = formProviderMethods;

    const formMeta = useContactInformationFormMeta(formProviderMethods);

    const emailValue = useWatch({ name: formMeta.fields.email.name, control: formProviderMethods.control });
    const [{ data: settingsData }] = useSettingsQuery();
    const termsAndConditionsArticleUrl = settingsData?.settings?.termsAndConditionsArticleUrl;
    const privacyPolicyArticleUrl = settingsData?.settings?.privacyPolicyArticleUrl;

    const isEmailFilledCorrectly = !!emailValue && !formState.errors.email;

    return (
        <div className={twJoin('mt-4', !isEmailFilledCorrectly && 'pointer-events-none opacity-50')}>
            <p className="mb-4">
                <Trans
                    defaultTrans="By clicking on the Send order button, you agree with <lnk1>terms and conditions</lnk1> of the e-shop and with the <lnk2>processing of privacy policy</lnk2>."
                    i18nKey="ContactInformationInfo"
                    components={{
                        lnk1: termsAndConditionsArticleUrl ? (
                            <Link
                                isExternal
                                aria-label={t('Go to terms and conditions article')}
                                href={termsAndConditionsArticleUrl}
                                target="_blank"
                            />
                        ) : (
                            <span className={linkPlaceholderTwClass} />
                        ),
                        lnk2: privacyPolicyArticleUrl ? (
                            <Link
                                isExternal
                                aria-label={t('Go to privacy policy article')}
                                href={privacyPolicyArticleUrl}
                                target="_blank"
                            />
                        ) : (
                            <span className={linkPlaceholderTwClass} />
                        ),
                    }}
                />
            </p>
            {settingsData?.settings?.heurekaEnabled && (
                <CheckboxControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.isWithoutHeurekaAgreement.name}
                    render={(checkbox) => <ChoiceFormLine>{checkbox}</ChoiceFormLine>}
                    checkboxProps={{
                        label: formMeta.fields.isWithoutHeurekaAgreement.label,
                    }}
                />
            )}
            {canManagePersonalData && (
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
            )}
        </div>
    );
};
