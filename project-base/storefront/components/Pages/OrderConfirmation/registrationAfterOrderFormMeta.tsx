import { yupResolver } from '@hookform/resolvers/yup';
import { Link } from 'components/Basic/Link/Link';
import { useTermsAndConditionsArticleUrlQuery } from 'graphql/requests/articles/queries/TermsAndConditionsArticleUrlQuery.generated';
import { useShopsysForm } from 'hooks/forms/useShopsysForm';
import Trans from 'next-translate/Trans';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';
import { UseFormReturn } from 'react-hook-form';
import { RegistrationAfterOrderFormType } from 'types/form';
import * as Yup from 'yup';

export const useRegistrationAfterOrderForm = (): [
    UseFormReturn<RegistrationAfterOrderFormType>,
    RegistrationAfterOrderFormType,
] => {
    const { t } = useTranslation();
    const resolver = yupResolver(
        Yup.object().shape<Record<keyof RegistrationAfterOrderFormType, any>>({
            password: Yup.string()
                .required(t('Please enter password'))
                .min(
                    6,
                    t('Password must be at least {{ count }} characters long', {
                        count: 6,
                    }),
                ),
            privacyPolicy: Yup.boolean().isTrue(t('You have to agree with our privacy policy')),
        }),
    );
    const defaultValues = { password: '', privacyPolicy: false };

    return [useShopsysForm(resolver, defaultValues), defaultValues];
};

type RegistrationAfterOrderFormMetaType = {
    formName: string;
    fields: {
        privacyPolicy: {
            name: 'privacyPolicy';
            label: string | JSX.Element;
            errorMessage: string | undefined;
        };
        password: {
            name: 'password';
            label: string;
            errorMessage: string | undefined;
        };
    };
};

export const useRegistrationAfterOrderFormMeta = (
    formProviderMethods: UseFormReturn<RegistrationAfterOrderFormType>,
): RegistrationAfterOrderFormMetaType => {
    const { t } = useTranslation();
    const [{ data: termsAndConditionsArticleUrlData }] = useTermsAndConditionsArticleUrlQuery();
    const termsAndConditionUrl = termsAndConditionsArticleUrlData?.termsAndConditionsArticle?.slug;

    const formMeta = useMemo(
        () => ({
            formName: 'registration-after-order-form',
            fields: {
                password: {
                    name: 'password' as const,
                    label: t('Password'),
                    errorMessage: formProviderMethods.formState.errors.password?.message,
                },
                privacyPolicy: {
                    name: 'privacyPolicy' as const,
                    label: (
                        <Trans
                            defaultTrans="I agree with <lnk1>terms and conditions</lnk1> and privacy policy"
                            i18nKey="I agree with terms and conditions and privacy policy"
                            components={{
                                lnk1:
                                    termsAndConditionUrl !== undefined ? (
                                        <Link isExternal href={termsAndConditionUrl} target="_blank" />
                                    ) : (
                                        <span />
                                    ),
                            }}
                        />
                    ),
                    errorMessage: formProviderMethods.formState.errors.privacyPolicy?.message,
                },
            },
        }),
        [
            formProviderMethods.formState.errors.password?.message,
            formProviderMethods.formState.errors.privacyPolicy?.message,
            termsAndConditionUrl,
            t,
        ],
    );

    return formMeta;
};
