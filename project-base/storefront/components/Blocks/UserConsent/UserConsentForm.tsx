import { useUserConsentForm, useUserConsentFormMeta } from './userConsentFormMeta';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Button } from 'components/Forms/Button/Button';
import { ToggleSwitchControlled } from 'components/Forms/ToggleSwitch/ToggleSwitchControlled';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import { onGtmConsentUpdateEventHandler } from 'gtm/handlers/onGtmConsentUpdateEventHandler';
import { getGtmConsentInfo } from 'gtm/utils/getGtmConsentInfo';
import Trans from 'next-translate/Trans';
import { JSX, ReactElement } from 'react';
import { FormProvider } from 'react-hook-form';
import { usePersistStore } from 'store/usePersistStore';
import { UserConsentFormType } from 'types/form';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type UserConsentFormProps = {
    onSetCallback?: () => void;
};

export const UserConsentForm: FC<UserConsentFormProps> = ({ onSetCallback }) => {
    const { t } = useTranslation();
    const [formProviderMethods] = useUserConsentForm();
    const formMeta = useUserConsentFormMeta();
    const [{ data: settingsData }] = useSettingsQuery();
    const userConsentPolicyArticleUrl = settingsData?.settings?.userConsentPolicyArticleUrl;
    const userConsent = usePersistStore((store) => store.userConsent);
    const updateUserConsent = usePersistStore((store) => store.updateUserConsent);

    const saveUserConsentChoices = () => {
        const formValues = formProviderMethods.getValues();
        updateUserConsent(formValues);
        onGtmConsentUpdateEventHandler(getGtmConsentInfo(userConsent));

        if (onSetCallback) {
            onSetCallback();
        }
    };

    const giveFullUserConsent = () => {
        for (const key in formMeta.fields) {
            formProviderMethods.setValue(key as keyof UserConsentFormType, true, { shouldValidate: true });
        }

        saveUserConsentChoices();
    };

    const rejectUserConsent = () => {
        for (const key in formMeta.fields) {
            formProviderMethods.setValue(key as keyof UserConsentFormType, false, { shouldValidate: true });
        }

        saveUserConsentChoices();
    };

    return (
        <FormProvider {...formProviderMethods}>
            <p className="sr-only" id="user-consent-policy-link">
                {t('This page is about the consent and tracking policy. You can read more about it here.')}
            </p>

            <p>
                <Trans
                    defaultTrans="To learn more, you can read our <link>consent and tracking policy</link>"
                    i18nKey="userConsentPolicyLink"
                    components={{
                        link: userConsentPolicyArticleUrl ? (
                            <ExtendedNextLink
                                aria-labelledby="user-consent-policy-link"
                                href={userConsentPolicyArticleUrl}
                                rel="noreferrer"
                                tabIndex={0}
                                target="_blank"
                                title={t('Consent and tracking policy')}
                            />
                        ) : (
                            <span />
                        ),
                    }}
                />
            </p>

            <div className="bg-background-more vl:p-8 flex flex-col gap-4 rounded-xl p-4">
                <ToggleSwitchControlled
                    ariaLabel={t('Toggle marketing consent', { ns: 'accessibility' })}
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.marketing.name}
                    render={(toggleSwitch) => (
                        <ToggleContent name={formMeta.fields.marketing.label} toggleSwitch={toggleSwitch} />
                    )}
                />

                <ToggleSwitchControlled
                    ariaLabel={t('Toggle statistics consent', { ns: 'accessibility' })}
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.statistics.name}
                    render={(toggleSwitch) => (
                        <ToggleContent name={formMeta.fields.statistics.label} toggleSwitch={toggleSwitch} />
                    )}
                />

                <ToggleSwitchControlled
                    ariaLabel={t('Toggle preferences consent', { ns: 'accessibility' })}
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.preferences.name}
                    render={(toggleSwitch) => (
                        <ToggleContent name={formMeta.fields.preferences.label} toggleSwitch={toggleSwitch} />
                    )}
                />
            </div>

            <div className="flex flex-wrap justify-end gap-3">
                <Button
                    aria-label={t('Submit form to save your choices', { ns: 'accessibility' })}
                    size="small"
                    onClick={saveUserConsentChoices}
                >
                    {t('Save choices')}
                </Button>

                <Button
                    aria-label={t('Submit form to accept all choices', { ns: 'accessibility' })}
                    size="small"
                    onClick={giveFullUserConsent}
                >
                    {t('Accept all')}
                </Button>

                <Button
                    aria-label={t('Submit form to reject all choices', { ns: 'accessibility' })}
                    size="small"
                    variant="inverted"
                    onClick={rejectUserConsent}
                >
                    {t('Reject all')}
                </Button>
            </div>
        </FormProvider>
    );
};

const ToggleContent: FC<{ name: string | ReactElement; toggleSwitch: JSX.Element }> = ({ toggleSwitch, name }) => (
    <div className="flex items-center justify-between">
        <span>{name}</span>
        {toggleSwitch}
    </div>
);
