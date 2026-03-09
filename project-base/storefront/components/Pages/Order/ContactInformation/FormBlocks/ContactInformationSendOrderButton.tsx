import { Link, linkPlaceholderTwClass } from 'components/Basic/Link/Link';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { FormBlockAgreements } from 'components/Forms/Form/Form';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/contactInformationFormMeta';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import Trans from 'next-translate/Trans';
import { useFormContext } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const ContactInformationSendOrderButton: FC = () => {
    const { t } = useTranslation();
    const formProviderMethods = useFormContext<ContactInformation>();
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);
    const { canManagePersonalData } = useAuthorization();

    const formMeta = useContactInformationFormMeta();

    const [{ data: settingsData }] = useSettingsQuery();
    const termsAndConditionsArticleUrl = settingsData?.settings?.termsAndConditionsArticleUrl;
    const privacyPolicyArticleUrl = settingsData?.settings?.privacyPolicyArticleUrl;

    return (
        <div className="flex flex-col">
            <FormBlockAgreements>
                {settingsData?.settings?.heurekaEnabled && (
                    <CheckboxControlled
                        control={formProviderMethods.control}
                        formName={formMeta.formName}
                        name={formMeta.fields.isWithoutHeurekaAgreement.name}
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
                        checkboxProps={{
                            label: formMeta.fields.newsletterSubscription.label,
                        }}
                        onChange={(event) =>
                            updateContactInformation({ newsletterSubscription: event.currentTarget.checked })
                        }
                    />
                )}
            </FormBlockAgreements>

            <div className="border-border-default vl:mx-20 mx-5 border-b" />

            <FormBlockAgreements className="pb-0">
                <div className="font-secondary text-sm font-semibold">
                    <Trans
                        defaultTrans="By clicking on the Send order button, you agree with <lnk1>terms and conditions</lnk1> of the e-shop and with the <lnk2>processing of privacy policy</lnk2>."
                        i18nKey="ContactInformationInfo"
                        components={{
                            lnk1: termsAndConditionsArticleUrl ? (
                                <Link
                                    aria-label={t('Go to terms and conditions article', { ns: 'accessibility' })}
                                    className="font-secondary inline text-sm font-semibold"
                                    href={termsAndConditionsArticleUrl}
                                    target="_blank"
                                />
                            ) : (
                                <span className={linkPlaceholderTwClass} />
                            ),
                            lnk2: privacyPolicyArticleUrl ? (
                                <Link
                                    aria-label={t('Go to privacy policy article', { ns: 'accessibility' })}
                                    className="font-secondary inline text-sm font-semibold"
                                    href={privacyPolicyArticleUrl}
                                    target="_blank"
                                />
                            ) : (
                                <span className={linkPlaceholderTwClass} />
                            ),
                        }}
                    />
                </div>
            </FormBlockAgreements>
        </div>
    );
};
