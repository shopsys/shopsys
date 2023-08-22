import { useUserConsentForm, useUserConsentFormMeta } from './userConsentFormMeta';
import { Heading } from 'components/Basic/Heading/Heading';
import { Button } from 'components/Forms/Button/Button';
import { ToggleSwitchControlled } from 'components/Forms/ToggleSwitch/ToggleSwitchControlled';
import { useCookiesArticleUrlQueryApi } from 'graphql/generated';
import { onGtmConsentUpdateEventHandler } from 'gtm/helpers/eventHandlers';
import { getGtmConsentInfo } from 'gtm/helpers/gtm';
import useTranslation from 'next-translate/useTranslation';
import Trans from 'next-translate/Trans';
import { useCallback } from 'react';
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
    const [{ data: cookiesArticleUrlData }] = useCookiesArticleUrlQueryApi();
    const cookiesArticleUrl = cookiesArticleUrlData?.cookiesArticle?.slug;
    const userConsent = usePersistStore((store) => store.userConsent);
    const setUserConsent = usePersistStore((store) => store.setUserConsent);

    const saveCookieChoices = useCallback(() => {
        const formValues = formProviderMethods.getValues();
        setUserConsent(formValues);
        onGtmConsentUpdateEventHandler(getGtmConsentInfo(userConsent));

        if (onSetCallback) {
            onSetCallback();
        }
    }, [formProviderMethods, onSetCallback, setUserConsent, userConsent]);

    const acceptAllCookieChoices = useCallback(() => {
        for (const key in formMeta.fields) {
            formProviderMethods.setValue(key as keyof UserConsentFormType, true, { shouldValidate: true });
        }

        saveCookieChoices();
    }, [formProviderMethods, saveCookieChoices, formMeta.fields]);

    const rejectAllCookieChoices = useCallback(() => {
        for (const key in formMeta.fields) {
            formProviderMethods.setValue(key as keyof UserConsentFormType, false, { shouldValidate: true });
        }

        saveCookieChoices();
    }, [formProviderMethods, saveCookieChoices, formMeta.fields]);

    return (
        <FormProvider {...formProviderMethods}>
            <Heading type="h2">{t('Cookie consent')}</Heading>
            <p>
                <Trans
                    i18nKey="cookiePolicyLink"
                    defaultTrans="To learn more, you can read our <link>cookie policy</link>"
                    components={{
                        link:
                            cookiesArticleUrl !== undefined ? (
                                <a href={cookiesArticleUrl} target="_blank" rel="noreferrer"></a>
                            ) : (
                                <span></span>
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
                <Button dataTestId="blocks-userconsent-save" size="small" variant="primary" onClick={saveCookieChoices}>
                    {t('Save choices')}
                </Button>
                <Button dataTestId="blocks-userconsent-accept" size="small" onClick={acceptAllCookieChoices}>
                    {t('Accept all')}
                </Button>
                <Button
                    dataTestId="blocks-userconsent-reject"
                    size="small"
                    variant="secondary"
                    onClick={rejectAllCookieChoices}
                >
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
