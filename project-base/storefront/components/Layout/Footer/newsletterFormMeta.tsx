import { yupResolver } from '@hookform/resolvers/yup';
import { Link, linkPlaceholderTwClass } from 'components/Basic/Link/Link';
import { usePrivacyPolicyArticleUrlQuery } from 'graphql/requests/articles/queries/PrivacyPolicyArticleUrlQuery.generated';
import Trans from 'next-translate/Trans';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';
import { UseFormReturn } from 'react-hook-form';
import { NewsletterFormType } from 'types/form';
import { useShopsysForm } from 'utils/forms/useShopsysForm';
import * as Yup from 'yup';

export const useNewsletterForm = (): [UseFormReturn<NewsletterFormType>, NewsletterFormType] => {
    const { t } = useTranslation();
    const resolver = yupResolver(
        Yup.object().shape<Record<keyof { email: string; privacyPolicy: boolean }, any>>({
            email: Yup.string().required(t('This field is required')).email(t('This value is not a valid email')),
            privacyPolicy: Yup.bool().oneOf([true], t('You have to agree with our privacy policy')),
        }),
    );
    const defaultValues = { email: '', privacyPolicy: false };

    return [useShopsysForm(resolver, defaultValues), defaultValues];
};

type NewsletterFormMetaType = {
    formName: string;
    messages: {
        error: string;
        success: string;
    };
    fields: {
        [key in keyof NewsletterFormType]: {
            name: key;
            label: string | JSX.Element;
            errorMessage: string | undefined;
        };
    };
};

export const useNewsletterFormMeta = (
    formProviderMethods: UseFormReturn<NewsletterFormType>,
): NewsletterFormMetaType => {
    const { t } = useTranslation();
    const [{ data: privacyPolicyArticleUrlData }] = usePrivacyPolicyArticleUrlQuery();
    const privacyPolicyArticleUrl = privacyPolicyArticleUrlData?.privacyPolicyArticle?.slug;

    const formMeta = useMemo(
        () => ({
            formName: 'newsletter-form',
            messages: {
                error: t('Could not subscribe to newsletter'),
                success: t('You have successfully subscribed to our newsletter'),
            },
            fields: {
                email: {
                    name: 'email' as const,
                    label: t('Your email'),
                    errorMessage: formProviderMethods.formState.errors.email?.message,
                },
                privacyPolicy: {
                    name: 'privacyPolicy' as const,
                    label: (
                        <Trans
                            defaultTrans="I take note of the <lnk1>processing of personal data</lnk1>."
                            i18nKey="PrivacyPolicyCheckbox"
                            components={{
                                lnk1: privacyPolicyArticleUrl ? (
                                    <Link isExternal href={privacyPolicyArticleUrl} target="_blank" />
                                ) : (
                                    <span className={linkPlaceholderTwClass} />
                                ),
                            }}
                        />
                    ),
                    errorMessage: formProviderMethods.formState.errors.privacyPolicy?.message,
                },
            },
        }),
        [
            t,
            formProviderMethods.formState.errors.email?.message,
            formProviderMethods.formState.errors.privacyPolicy?.message,
            privacyPolicyArticleUrl,
        ],
    );

    return formMeta;
};
