'use client';

import { useUserConsentForm, useUserConsentFormMeta } from './userConsentFormMeta';
import Trans from 'app/_utils/translation/Trans';
import { Button } from 'components/Forms/Button/Button';
import { ToggleSwitchControlled } from 'components/Forms/ToggleSwitch/ToggleSwitchControlled';
import { useAppConfig } from 'components/providers/AppConfigProvider';
import { useTranslation } from 'components/providers/TranslationProvider';
import { FormProvider } from 'react-hook-form';
import { usePersistStore } from 'store/usePersistStore';
import { UserConsentFormType } from 'types/form';

type UserConsentFormProps = {
    onSetCallback?: () => void;
};

// TODO: add gtm
export const UserConsentForm: FC<UserConsentFormProps> = ({ onSetCallback }) => {
    const { t } = useTranslation();
    const [formProviderMethods] = useUserConsentForm();
    const formMeta = useUserConsentFormMeta();
    const settings = useAppConfig((settingsData) => settingsData.settings);
    const userConsentPolicyArticleUrl = settings.userConsentPolicyArticleUrl;
    // const userConsent = usePersistStore((store) => store.userConsent);
    const updateUserConsent = usePersistStore((store) => store.updateUserConsent);

    const saveUserConsentChoices = () => {
        const formValues = formProviderMethods.getValues();
        updateUserConsent(formValues);
        // onGtmConsentUpdateEventHandler(getGtmConsentInfo(userConsent));

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
            <div className="h2 mb-3">{t('User consent')}</div>

            <p>
                <Trans
                    defaultTrans="To learn more, you can read our <link>consent and tracking policy</link>"
                    i18nKey="userConsentPolicyLink"
                    components={{
                        link: userConsentPolicyArticleUrl ? (
                            <a
                                aria-label={t('Go to user consent update page')}
                                href={userConsentPolicyArticleUrl}
                                rel="noreferrer"
                                target="_blank"
                            />
                        ) : (
                            <span />
                        ),
                    }}
                />
            </p>

            <ToggleSwitchControlled
                ariaLabel={t('Marketing')}
                control={formProviderMethods.control}
                formName={formMeta.formName}
                name={formMeta.fields.marketing.name}
                render={(toggleSwitch) => <ToggleContent name={t('Marketing')} toggleSwitch={toggleSwitch} />}
            />

            <ToggleSwitchControlled
                ariaLabel={t('Statistics')}
                control={formProviderMethods.control}
                formName={formMeta.formName}
                name={formMeta.fields.statistics.name}
                render={(toggleSwitch) => <ToggleContent name={t('Statistics')} toggleSwitch={toggleSwitch} />}
            />

            <ToggleSwitchControlled
                ariaLabel={t('Preferences')}
                control={formProviderMethods.control}
                formName={formMeta.formName}
                name={formMeta.fields.preferences.name}
                render={(toggleSwitch) => <ToggleContent name={t('Preferences')} toggleSwitch={toggleSwitch} />}
            />

            <div className="mt-10 mb-5 flex flex-wrap justify-end gap-3">
                <Button size="small" onClick={saveUserConsentChoices}>
                    {t('Save choices')}
                </Button>

                <Button size="small" onClick={giveFullUserConsent}>
                    {t('Accept all')}
                </Button>

                <Button size="small" variant="inverted" onClick={rejectUserConsent}>
                    {t('Reject all')}
                </Button>
            </div>
        </FormProvider>
    );
};

const ToggleContent: FC<{ name: string; toggleSwitch: React.ReactNode }> = ({ toggleSwitch, name }) => (
    <div className="border-borderAccent my-2 flex justify-between border-b">
        <span className="text-xl">{name}</span>
        {toggleSwitch}
    </div>
);
