import { yupResolver } from '@hookform/resolvers/yup';
import Link from 'components/Basic/Link';
import { useShopsysForm } from 'hooks/forms/UseShopsysForm';
import { useGetTermsAndConditionsUrl } from 'hooks/routes/useGetTermsAndConditionsUrl';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Trans from 'next-translate/Trans';
import { UseFormReturn } from 'react-hook-form';
import { RegistrationAfterOrderFormType } from 'types/form';
import * as Yup from 'yup';

export const useRegistrationAfterOrderForm = (): [
    UseFormReturn<RegistrationAfterOrderFormType>,
    RegistrationAfterOrderFormType,
] => {
    const t = useTypedTranslationFunction();
    const resolver = yupResolver(
        Yup.object().shape({
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
        [key in keyof RegistrationAfterOrderFormType]: {
            name: key;
            label: string | JSX.Element;
            errorMessage: string | undefined;
        };
    };
};

export const useRegistrationAfterOrderFormMeta = (
    formProviderMethods: UseFormReturn<RegistrationAfterOrderFormType>,
): RegistrationAfterOrderFormMetaType => {
    const t = useTypedTranslationFunction();
    const termsAndConditionUrl = useGetTermsAndConditionsUrl();

    const formMeta = {
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
                        i18nKey="I agree with terms and conditions and privacy policy"
                        defaultTrans="I agree with <lnk1>terms and conditions</lnk1> and privacy policy"
                        components={{
                            lnk1: <Link href={termsAndConditionUrl} linkType="external" target="_blank" />,
                        }}
                    />
                ),
                errorMessage: formProviderMethods.formState.errors.privacyPolicy?.message,
            },
        },
    };

    return formMeta;
};
