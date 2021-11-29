import * as Yup from 'yup';
import { Trans } from 'react-i18next';
import { UseFormReturn } from 'react-hook-form';
import { useShopsysForm } from 'hooks/forms/UseShopsysForm';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { yupResolver } from '@hookform/resolvers/yup';

export type RegistrationAfterOrderFormType = {
    password: string;
    privacyPolicy: boolean;
};

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
                        postProcess: 'interval',
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
                    <Trans i18nKey="I agree with terms and conditions and privacy policy">
                        I agree with
                        <a href="/">terms and conditions</a>
                        and privacy policy
                    </Trans>
                ),
                errorMessage: formProviderMethods.formState.errors.privacyPolicy?.message,
            },
        },
    };

    return formMeta;
};
