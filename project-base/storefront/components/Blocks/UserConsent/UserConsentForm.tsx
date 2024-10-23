import { useUserConsentForm, useUserConsentFormMeta } from './userConsentFormMeta';
import { Button } from 'components/Forms/Button/Button';
import { ToggleSwitchControlled } from 'components/Forms/ToggleSwitch/ToggleSwitchControlled';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import { onGtmConsentUpdateEventHandler } from 'gtm/handlers/onGtmConsentUpdateEventHandler';
import { getGtmConsentInfo } from 'gtm/utils/getGtmConsentInfo';
import Trans from 'next-translate/Trans';
import useTranslation from 'next-translate/useTranslation';
import { JSX } from 'react';
import { FormProvider } from 'react-hook-form';
import { usePersistStore } from 'store/usePersistStore';
import { UserConsentFormType } from 'types/form';

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
            <div className="h2 mb-3">{t('User consent')}</div>

            <p>
                <Trans
                    defaultTrans="To learn more, you can read our <link>consent and tracking policy</link>"
                    i18nKey="userConsentPolicyLink"
                    components={{
                        link: userConsentPolicyArticleUrl ? (
                            <a href={userConsentPolicyArticleUrl} rel="noreferrer" target="_blank" />
                        ) : (
                            <span />
                        ),
                    }}
                />
            </p>

            <ToggleSwitchControlled
                control={formProviderMethods.control}
                formName={formMeta.formName}
                name={formMeta.fields.marketing.name}
                render={(toggleSwitch) => <ToggleContent name={t('Marketing')} toggleSwitch={toggleSwitch} />}
            />

            <ToggleSwitchControlled
                control={formProviderMethods.control}
                formName={formMeta.formName}
                name={formMeta.fields.statistics.name}
                render={(toggleSwitch) => <ToggleContent name={t('Statistics')} toggleSwitch={toggleSwitch} />}
            />

            <ToggleSwitchControlled
                control={formProviderMethods.control}
                formName={formMeta.formName}
                name={formMeta.fields.preferences.name}
                render={(toggleSwitch) => <ToggleContent name={t('Preferences')} toggleSwitch={toggleSwitch} />}
            />

            <div className="mb-5 mt-10 flex flex-wrap justify-end gap-3">
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

const ToggleContent: FC<{ name: string; toggleSwitch: JSX.Element }> = ({ toggleSwitch, name }) => (
    <div className="my-2 flex justify-between border-b border-borderAccent">
        <span className="text-xl">{name}</span>
        {toggleSwitch}
    </div>
);
