import * as Yup from 'yup';
import { NewPasswordFormType } from 'types/form';
import { UseFormReturn } from 'react-hook-form';
import { useShopsysForm } from 'hooks/forms/UseShopsysForm';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { yupResolver } from '@hookform/resolvers/yup';

export const useRecoveryPasswordForm = (): [UseFormReturn<NewPasswordFormType>, NewPasswordFormType] => {
    const t = useTypedTranslationFunction();
    const resolver = yupResolver(
        Yup.object().shape({
            newPasswordFirst: Yup.string()
                .required(t('Fill first password'))
                .min(
                    6,
                    t('Password must be at least {{ count }} characters long', {
                        postProcess: 'interval',
                        count: 6,
                    }),
                ),
            newPasswordSecond: Yup.string().when('newPasswordFirst', {
                is: (newPasswordFirst: string) => newPasswordFirst.length > 0,
                then: Yup.string()
                    .required(t('Fill second password'))
                    .oneOf([Yup.ref('newPasswordFirst'), null], t('Passwords must match'))
                    .min(
                        6,
                        t('Password must be at least {{ count }} characters long', {
                            postProcess: 'interval',
                            count: 6,
                        }),
                    ),
                otherwise: Yup.string(),
            }),
        }),
    );
    const defaultValues = {
        newPasswordFirst: '',
        newPasswordSecond: '',
    };

    return [useShopsysForm(resolver, defaultValues), defaultValues];
};

type NewPasswordFormMetaType = {
    formName: string;
    messages: {
        error: string;
        success: string;
    };
    fields: {
        [key in keyof NewPasswordFormType]: {
            name: key;
            label: string;
            errorMessage: string | undefined;
        };
    };
};

export const useRecoveryPasswordFormMeta = (
    formProviderMethods: UseFormReturn<NewPasswordFormType>,
): NewPasswordFormMetaType => {
    const t = useTypedTranslationFunction();

    const formMeta = {
        formName: 'new-password-form',
        messages: {
            error: t('Error occured while changing your password'),
            success: t('Your password has been changed and you are now logged in'),
        },
        fields: {
            newPasswordFirst: {
                name: 'newPasswordFirst' as const,
                label: t('New password'),
                errorMessage: formProviderMethods.formState.errors.newPasswordFirst?.message,
            },
            newPasswordSecond: {
                name: 'newPasswordSecond' as const,
                label: t('New password again'),
                errorMessage: formProviderMethods.formState.errors.newPasswordSecond?.message,
            },
        },
    };

    return formMeta;
};
