import { yupResolver } from '@hookform/resolvers/yup';
import { useShopsysForm } from 'hooks/forms/UseShopsysForm';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
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
            label: string;
            errorMessage: string | undefined;
        };
    };
};

export const useNewsletterFormMeta = (
    formProviderMethods: UseFormReturn<NewsletterFormType>,
): NewsletterFormMetaType => {
    const t = useTypedTranslationFunction();

    const formMeta = {
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
                label: t('I take note of the processing of personal data'),
                errorMessage: formProviderMethods.formState.errors.privacyPolicy?.message,
            },
        },
    };

    return formMeta;
};
