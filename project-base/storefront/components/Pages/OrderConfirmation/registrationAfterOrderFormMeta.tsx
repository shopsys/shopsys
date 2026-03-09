import { yupResolver } from '@hookform/resolvers/yup';
import { Link, linkPlaceholderTwClass } from 'components/Basic/Link/Link';
import { validatePassword, validatePasswordConfirm, validatePrivacyPolicy } from 'components/Forms/validationRules';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import Trans from 'next-translate/Trans';
import { UseFormReturn } from 'react-hook-form';
import { RegistrationAfterOrderFormType } from 'types/form';
import { FormMeta } from 'types/formMeta';
import { createFields } from 'utils/forms/createFields';
import { useFormWrapper } from 'utils/forms/useFormWrapper';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import * as Yup from 'yup';

export const useRegistrationAfterOrderForm = (): [
    UseFormReturn<RegistrationAfterOrderFormType>,
    RegistrationAfterOrderFormType,
] => {
    const { t } = useTranslation();
    const resolver = yupResolver(
        Yup.object().shape<Record<keyof RegistrationAfterOrderFormType, any>>({
            password: validatePassword(t),
            passwordConfirm: validatePasswordConfirm(t),
            privacyPolicy: validatePrivacyPolicy(t),
        }),
    );
    const defaultValues = { password: '', passwordConfirm: '', privacyPolicy: false };

    return [useFormWrapper(resolver, defaultValues), defaultValues];
};

export const useRegistrationAfterOrderFormMeta = (): FormMeta<RegistrationAfterOrderFormType> => {
    const { t } = useTranslation();
    const [{ data: settingsData }] = useSettingsQuery();
    const termsAndConditionUrl = settingsData?.settings?.termsAndConditionsArticleUrl;
    return {
        formName: 'registration-after-order-form',
        messages: {},
        fields: createFields<RegistrationAfterOrderFormType>({
            password: t('Password'),
            passwordConfirm: t('Password again'),
            privacyPolicy: (
                <Trans
                    defaultTrans="I agree with <lnk1>terms and conditions</lnk1> and privacy policy"
                    i18nKey="I agree with terms and conditions and privacy policy"
                    components={{
                        lnk1: termsAndConditionUrl ? (
                            <Link className="inline text-sm" href={termsAndConditionUrl} target="_blank" />
                        ) : (
                            <span className={linkPlaceholderTwClass} />
                        ),
                    }}
                />
            ),
        }),
    };
};
