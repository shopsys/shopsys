import { yupResolver } from '@hookform/resolvers/yup';
import { Link } from 'components/Basic/Link/Link';
import { usePrivacyPolicyArticleUrlQueryApi } from 'graphql/requests/articles/queries/PrivacyPolicyArticleUrlQuery.generated';
import { useShopsysForm } from 'hooks/forms/useShopsysForm';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import Trans from 'next-translate/Trans';
import { useMemo } from 'react';
import { UseFormReturn } from 'react-hook-form';
import { NewsletterFormType } from 'types/form';
import * as Yup from 'yup';

export const useNewsletterForm = (): [UseFormReturn<NewsletterFormType>, NewsletterFormType] => {
    const t = useTypedTranslationFunction();
    const resolver = yupResolver(
        Yup.object().shape({
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
    const t = useTypedTranslationFunction();
    const [{ data: privacyPolicyArticleUrlData }] = usePrivacyPolicyArticleUrlQueryApi();
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
                            i18nKey="PrivacyPolicyCheckbox"
                            defaultTrans="I take note of the <lnk1>processing of personal data</lnk1>."
                            components={{
                                lnk1:
                                    privacyPolicyArticleUrl !== undefined ? (
                                        <Link href={privacyPolicyArticleUrl} isExternal target="_blank" />
                                    ) : (
                                        <span></span>
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
