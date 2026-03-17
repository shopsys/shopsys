import { yupResolver } from '@hookform/resolvers/yup';
import { Link, linkPlaceholderTwClass } from 'components/Basic/Link/Link';
import { validateEmail, validatePrivacyPolicy } from 'components/Forms/validationRules';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import Trans from 'next-translate/Trans';
import { UseFormReturn } from 'react-hook-form';
import { ContactFormType } from 'types/form';
import { FormMeta } from 'types/formMeta';
import { createFields } from 'utils/forms/createFields';
import { useFormWrapper } from 'utils/forms/useFormWrapper';
import { useOnFinishHydrationDefaultValuesPrefill } from 'utils/forms/useOnFinishHydrationDefaultValuesPrefill';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import * as Yup from 'yup';

export const useContactForm = (): [UseFormReturn<ContactFormType>, ContactFormType] => {
    const { t } = useTranslation();
    const user = useCurrentCustomerData();

    const resolver = yupResolver(
        Yup.object().shape<Record<keyof ContactFormType, any>>({
            email: validateEmail(t),
            name: Yup.string().required(t('Please enter your name')),
            message: Yup.string().required(t('Please enter a message')),
            privacyPolicy: validatePrivacyPolicy(t),
        }),
    );
    const defaultValues = {
        email: user?.email ?? '',
        name: user?.firstName ?? '',
        message: '',
        privacyPolicy: false,
    };
    const formProviderMethods = useFormWrapper(resolver, defaultValues);

    useOnFinishHydrationDefaultValuesPrefill(defaultValues, formProviderMethods);

    return [formProviderMethods, defaultValues];
};

export const useContactFormMeta = (): FormMeta<ContactFormType, { error: string; success: string }> => {
    const { t } = useTranslation();
    const [{ data: settingsData }] = useSettingsQuery();
    const privacyPolicyUrl = settingsData?.settings?.privacyPolicyArticleUrl;
    return {
        formName: 'contact-form',
        messages: {
            error: t('The message could not be sent'),
            success: t('Thank you! Your message was successfully sent.'),
        },
        fields: createFields<ContactFormType>({
            email: t('Your email'),
            name: t('Your name'),
            message: t('Message'),
            privacyPolicy: (
                <Trans
                    defaultTrans="I agree with <lnk1>processing of privacy policy</lnk1>."
                    i18nKey="GdprAgreementCheckbox"
                    components={{
                        lnk1: privacyPolicyUrl ? (
                            <Link className="inline text-sm" href={privacyPolicyUrl} target="_blank" />
                        ) : (
                            <span className={linkPlaceholderTwClass} />
                        ),
                    }}
                />
            ),
        }),
    };
};
