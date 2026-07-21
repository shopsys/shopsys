import { Link, linkPlaceholderTwClass } from 'components/Basic/Link/Link';
import { validateEmail, validatePrivacyPolicy } from 'components/Forms/validationRules';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import Trans from 'next-translate/Trans';
import { UseFormReturn } from 'react-hook-form';
import { NewsletterFormType } from 'types/form';
import { FormMeta } from 'types/formMeta';
import { createFields } from 'utils/forms/createFields';
import { useFormWrapper } from 'utils/forms/useFormWrapper';
import { yupResolver } from 'utils/forms/yupResolver';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import * as Yup from 'yup';

export const useNewsletterForm = (): [UseFormReturn<NewsletterFormType>, NewsletterFormType] => {
    const { t } = useTranslation();
    const resolver = yupResolver<NewsletterFormType>(
        Yup.object().shape<Record<keyof NewsletterFormType, any>>({
            email: validateEmail(t),
            privacyPolicy: validatePrivacyPolicy(t),
        }),
    );
    const defaultValues = { email: '', privacyPolicy: false };

    return [useFormWrapper(resolver, defaultValues), defaultValues];
};

export const useNewsletterFormMeta = (): FormMeta<NewsletterFormType, { error: string; success: string }> => {
    const { t } = useTranslation();
    const [{ data: settingsData }] = useSettingsQuery();
    const privacyPolicyArticleUrl = settingsData?.settings?.privacyPolicyArticleUrl;
    return {
        formName: 'newsletter-form',
        messages: {
            error: t('Could not subscribe to newsletter'),
            success: t('You have successfully subscribed to our newsletter'),
        },
        fields: createFields<NewsletterFormType>({
            email: t('Your email'),
            privacyPolicy: (
                <Trans
                    defaultTrans="I take note of the <lnk1>processing of personal data</lnk1>."
                    i18nKey="PrivacyPolicyCheckbox"
                    components={{
                        lnk1: privacyPolicyArticleUrl ? (
                            <Link
                                aria-label={t('Go to privacy policy article', { ns: 'accessibility' })}
                                className="inline text-sm"
                                href={privacyPolicyArticleUrl}
                                target="_blank"
                            />
                        ) : (
                            <span className={linkPlaceholderTwClass} />
                        ),
                    }}
                />
            ),
        }),
    };
};
