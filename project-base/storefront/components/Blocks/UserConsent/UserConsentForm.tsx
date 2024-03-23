import { useUserConsentForm, useUserConsentFormMeta } from './userConsentFormMeta';
import { Button } from 'components/Forms/Button/Button';
import { ToggleSwitchControlled } from 'components/Forms/ToggleSwitch/ToggleSwitchControlled';
import { useCookiesArticleUrlQuery } from 'graphql/requests/articles/queries/CookiesArticleUrlQuery.generated';
import { onGtmConsentUpdateEventHandler } from 'gtm/helpers/eventHandlers';
import { getGtmConsentInfo } from 'gtm/helpers/gtm';
import Trans from 'next-translate/Trans';
import useTranslation from 'next-translate/useTranslation';
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
    const [{ data: cookiesArticleUrlData }] = useCookiesArticleUrlQuery();
    const cookiesArticleUrl = cookiesArticleUrlData?.cookiesArticle?.slug;
    const userConsent = usePersistStore((store) => store.userConsent);
    const updateUserConsent = usePersistStore((store) => store.updateUserConsent);

    const saveCookieChoices = () => {
        const formValues = formProviderMethods.getValues();
        updateUserConsent(formValues);
        onGtmConsentUpdateEventHandler(getGtmConsentInfo(userConsent));

        if (onSetCallback) {
            onSetCallback();
        }
    };

    const acceptAllCookieChoices = () => {
        for (const key in formMeta.fields) {
            formProviderMethods.setValue(key as keyof UserConsentFormType, true, { shouldValidate: true });
        }

        saveCookieChoices();
    };

    const rejectAllCookieChoices = () => {
        for (const key in formMeta.fields) {
            formProviderMethods.setValue(key as keyof UserConsentFormType, false, { shouldValidate: true });
        }

        saveCookieChoices();
    };

    return (
        <FormProvider {...formProviderMethods}>
            <div className="h2 mb-3">{t('Cookie consent')}</div>

            <p>
                <Trans
                    defaultTrans="To learn more, you can read our <link>cookie policy</link>"
                    i18nKey="cookiePolicyLink"
                    components={{
                        link:
                            cookiesArticleUrl !== undefined ? (
                                <a href={cookiesArticleUrl} rel="noreferrer" target="_blank" />
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

            <div className="mt-10 mb-5 flex flex-wrap justify-end gap-3">
                <Button size="small" variant="primary" onClick={saveCookieChoices}>
                    {t('Save choices')}
                </Button>

                <Button size="small" onClick={acceptAllCookieChoices}>
                    {t('Accept all')}
                </Button>

                <Button size="small" variant="secondary" onClick={rejectAllCookieChoices}>
                    {t('Reject all')}
                </Button>
            </div>
        </FormProvider>
    );
};

const ToggleContent: FC<{ name: string; toggleSwitch: JSX.Element }> = ({ toggleSwitch, name }) => (
    <div className="my-2 flex justify-between border-b border-greyLight">
        <span className="text-xl">{name}</span>
        {toggleSwitch}
    </div>
);
