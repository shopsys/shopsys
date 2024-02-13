import { yupResolver } from '@hookform/resolvers/yup';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { useShopsysForm } from 'hooks/forms/useShopsysForm';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';
import { UseFormReturn } from 'react-hook-form';
import { ContactFormType } from 'types/form';
import * as Yup from 'yup';

export const useContactForm = (): [UseFormReturn<ContactFormType>, ContactFormType] => {
    const { t } = useTranslation();
    const user = useCurrentCustomerData();

    const resolver = yupResolver(
        Yup.object().shape<Record<keyof ContactFormType, any>>({
            email: Yup.string().required(t('Please enter email')).email(t('This value is not a valid email')).min(5),
            name: Yup.string().required(t('Please enter your name')),
            message: Yup.string().required(t('Please enter a message')),
        }),
    );
    const defaultValues = {
        email: user?.email ?? '',
        name: user?.firstName ?? '',
        message: '',
    };

    return [useShopsysForm(resolver, defaultValues), defaultValues];
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
            label: string;
            errorMessage: string | undefined;
        };
    };
};

export const useContactFormMeta = (formProviderMethods: UseFormReturn<ContactFormType>): ContactFormMetaType => {
    const { t } = useTranslation();
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
            },
        }),
        [errors.email?.message, errors.name?.message, errors.message?.message, t],
    );

    return formMeta;
};
