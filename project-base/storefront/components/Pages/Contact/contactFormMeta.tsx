import { yupResolver } from '@hookform/resolvers/yup';
import { Link, linkPlaceholderTwClass } from 'components/Basic/Link/Link';
import { validateEmail } from 'components/Forms/validationRules';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { usePrivacyPolicyArticleUrlQuery } from 'graphql/requests/articles/queries/PrivacyPolicyArticleUrlQuery.generated';
import Trans from 'next-translate/Trans';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';
import { UseFormReturn } from 'react-hook-form';
import { ContactFormType } from 'types/form';
import { useOnFinishHydrationDefaultValuesPrefill } from 'utils/forms/useOnFinishHydrationDefaultValuesPrefill';
import { useShopsysForm } from 'utils/forms/useShopsysForm';
import * as Yup from 'yup';

export const useContactForm = (): [UseFormReturn<ContactFormType>, ContactFormType] => {
    const { t } = useTranslation();
    const user = useCurrentCustomerData();

    const resolver = yupResolver(
        Yup.object().shape<Record<keyof ContactFormType, any>>({
            email: validateEmail(t),
            name: Yup.string().required(t('Please enter your name')),
            message: Yup.string().required(t('Please enter a message')),
            privacyPolicy: Yup.boolean().isTrue(t('You have to agree with our privacy policy')),
        }),
    );
    const defaultValues = {
        email: user?.email ?? '',
        name: user?.firstName ?? '',
        message: '',
        privacyPolicy: false,
    };
    const formProviderMethods = useShopsysForm(resolver, defaultValues);

    useOnFinishHydrationDefaultValuesPrefill(defaultValues, formProviderMethods);

    return [formProviderMethods, defaultValues];
};

type ContactFormMetaType = {
    formName: string;
    messages: {
        error: string;
        success: string;
    };
    fields: {
        [key in keyof ContactFormType]: {
            name: key;
            label: string | JSX.Element;
            errorMessage: string | undefined;
        };
    };
};

export const useContactFormMeta = (formProviderMethods: UseFormReturn<ContactFormType>): ContactFormMetaType => {
    const { t } = useTranslation();
    const [{ data: privacyPolicyArticleUrlData }] = usePrivacyPolicyArticleUrlQuery();
    const privacyPolicyUrl = privacyPolicyArticleUrlData?.privacyPolicyArticle?.slug;
    const errors = formProviderMethods.formState.errors;

    const formMeta = useMemo(
        () => ({
            formName: 'contact-form',
            messages: {
                error: t('The message could not be sent'),
                success: t('Thank you! Your message was successfully sent.'),
            },
            fields: {
                email: {
                    name: 'email' as const,
                    label: t('Your email'),
                    errorMessage: errors.email?.message,
                },
                name: {
                    name: 'name' as const,
                    label: t('Your name'),
                    errorMessage: errors.name?.message,
                },
                message: {
                    name: 'message' as const,
                    label: t('Message'),
                    errorMessage: errors.message?.message,
                },
                privacyPolicy: {
                    name: 'privacyPolicy' as const,
                    label: (
                        <Trans
                            defaultTrans="I agree with <lnk1>processing of privacy policy</lnk1>."
                            i18nKey="GdprAgreementCheckbox"
                            components={{
                                lnk1:
                                    privacyPolicyUrl !== undefined ? (
                                        <Link isExternal href={privacyPolicyUrl} target="_blank" />
                                    ) : (
                                        <span className={linkPlaceholderTwClass} />
                                    ),
                            }}
                        />
                    ),
                    errorMessage: errors.privacyPolicy?.message,
                },
            },
        }),
        [errors.email?.message, errors.name?.message, errors.message?.message, t],
    );

    return formMeta;
};
