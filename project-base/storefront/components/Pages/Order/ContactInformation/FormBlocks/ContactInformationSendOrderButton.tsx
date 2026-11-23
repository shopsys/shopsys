import { Link } from 'components/Basic/Link/Link';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { FormBlockAgreements } from 'components/Forms/Form/Form';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/contactInformationFormMeta';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import Trans from 'next-translate/Trans';
import { useFormContext } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';

export const ContactInformationSendOrderButton: FC = () => {
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

            <div className="mx-5 vl:mx-20 border-border-default border-b" />

            <FormBlockAgreements className="pb-0">
                <div className="font-secondary font-semibold text-sm">
                    <Trans
                        defaultTrans="By clicking on the Send order button, you agree with <lnk1>terms and conditions</lnk1> of the e-shop and with the <lnk2>processing of privacy policy</lnk2>."
                        i18nKey="ContactInformationInfo"
                        components={{
                            lnk1: termsAndConditionsArticleUrl ? (
                                <Link href={termsAndConditionsArticleUrl} target="_blank" />
                            ) : (
                                <span />
                            ),
                            lnk2: privacyPolicyArticleUrl ? (
                                <Link href={privacyPolicyArticleUrl} target="_blank" />
                            ) : (
                                <span />
                            ),
                        }}
                    />
                </div>
            </FormBlockAgreements>
        </div>
    );
};
