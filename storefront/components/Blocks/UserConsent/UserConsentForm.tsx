import { useUserConsentForm, useUserConsentFormMeta } from './formMeta';
import { ConsentButtonsRowStyled, ConsentNameStyled, ConsentRowStyled } from './UserConsentForm.style';
import { Heading } from 'components/Basic/Heading/Heading';
import { Button } from 'components/Forms/Button/Button';
import { ToggleSwitch } from 'components/Forms/ToggleSwitch/ToggleSwitch';
import { setUserConsentCookie } from 'helpers/cookies/setUserConsentCookie';
import { onConsentUpdateGtmEventHandler } from 'helpers/gtm/eventHandlers';
import { getGtmConsentInfo } from 'helpers/gtm/gtm';
import { useGetCookiesUrl } from 'hooks/routes/useGetCookiesUrl';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import Trans from 'next-translate/Trans';
import { FC, useCallback } from 'react';
import { Controller, FormProvider } from 'react-hook-form';
import { UserConsentFormType } from 'types/form';

type UserConsentFormProps = {
    onSetCallback?: () => void;
};

export const UserConsentForm: FC<UserConsentFormProps> = ({ onSetCallback }) => {
    const t = useTypedTranslationFunction();
    const [formProviderMethods] = useUserConsentForm();
    const formMeta = useUserConsentFormMeta();
    const cookiePolicyUrl = useGetCookiesUrl();

    const saveCookieChoices = useCallback(() => {
        const formValues = formProviderMethods.getValues();
        setUserConsentCookie(formValues);
        onConsentUpdateGtmEventHandler(getGtmConsentInfo());

        if (onSetCallback) {
            onSetCallback();
        }
    }, [formProviderMethods, onSetCallback]);

    const acceptAllCookieChoices = useCallback(() => {
        for (const key in formMeta.fields) {
            formProviderMethods.setValue(key as keyof UserConsentFormType, true);
        }

        saveCookieChoices();
    }, [formProviderMethods, saveCookieChoices, formMeta.fields]);

    const rejectAllCookieChoices = useCallback(() => {
        for (const key in formMeta.fields) {
            formProviderMethods.setValue(key as keyof UserConsentFormType, false);
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
                        link: <a href={cookiePolicyUrl} target="_blank" rel="noreferrer"></a>,
                    }}
                />
            </p>
            <ConsentRowStyled>
                <ConsentNameStyled>{t('Marketing')}</ConsentNameStyled>
                <Controller
                    name={formMeta.fields.marketing.name}
                    render={({ field }) => <ToggleSwitch id={formMeta.fields.marketing.name} fieldRef={field} />}
                />
            </ConsentRowStyled>
            <ConsentRowStyled>
                <ConsentNameStyled>{t('Statistics')}</ConsentNameStyled>
                <Controller
                    name={formMeta.fields.statistics.name}
                    render={({ field }) => <ToggleSwitch id={formMeta.fields.statistics.name} fieldRef={field} />}
                />
            </ConsentRowStyled>
            <ConsentRowStyled>
                <ConsentNameStyled>{t('Preferences')}</ConsentNameStyled>
                <Controller
                    name={formMeta.fields.preferences.name}
                    render={({ field }) => <ToggleSwitch id={formMeta.fields.preferences.name} fieldRef={field} />}
                />
            </ConsentRowStyled>
            <ConsentButtonsRowStyled>
                <Button
                    testIdentifier="blocks-userconsent-save"
                    type="button"
                    size="small"
                    variant="primary"
                    onClick={saveCookieChoices}
                >
                    {t('Save choices')}
                </Button>
                <Button
                    testIdentifier="blocks-userconsent-accept"
                    type="button"
                    size="small"
                    onClick={acceptAllCookieChoices}
                >
                    {t('Accept all')}
                </Button>
                <Button
                    testIdentifier="blocks-userconsent-reject"
                    type="button"
                    size="small"
                    variant="secondary"
                    onClick={rejectAllCookieChoices}
                >
                    {t('Reject all')}
                </Button>
            </ConsentButtonsRowStyled>
        </FormProvider>
    );
};
