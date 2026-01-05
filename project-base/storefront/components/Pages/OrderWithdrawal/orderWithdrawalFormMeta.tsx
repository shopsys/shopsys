import { yupResolver } from '@hookform/resolvers/yup';
import {
    validateEmail,
    validateFirstName,
    validateLastName,
    validateTelephone,
} from 'components/Forms/validationRules';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { TypeOrderWithdrawalDataFragment } from 'graphql/requests/orders/fragments/OrderWithdrawalDataFragment.generated';
import { useMemo } from 'react';
import { UseFormReturn } from 'react-hook-form';
import { OrderWithdrawalFormType } from 'types/form';
import { useFormWrapper } from 'utils/forms/useFormWrapper';
import { useOnFinishHydrationDefaultValuesPrefill } from 'utils/forms/useOnFinishHydrationDefaultValuesPrefill';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import * as Yup from 'yup';

export const useOrderWithdrawalForm = (
    order: TypeOrderWithdrawalDataFragment | undefined,
): [UseFormReturn<OrderWithdrawalFormType>, OrderWithdrawalFormType] => {
    const { t } = useTranslation();
    const user = useCurrentCustomerData();

    const resolver = yupResolver(
        Yup.object().shape<Record<keyof OrderWithdrawalFormType, any>>({
            firstName: validateFirstName(t),
            lastName: validateLastName(t),
            email: validateEmail(t),
            telephone: validateTelephone(t),
            note: Yup.string().optional(),
        }),
    );

    const defaultValues: OrderWithdrawalFormType = {
        firstName: order?.firstName ?? user?.firstName ?? '',
        lastName: order?.lastName ?? user?.lastName ?? '',
        email: order?.email ?? user?.email ?? '',
        telephone: order?.telephone ?? user?.telephone ?? '',
        note: '',
    };

    const formProviderMethods = useFormWrapper(resolver, defaultValues);
    useOnFinishHydrationDefaultValuesPrefill(defaultValues, formProviderMethods);

    return [formProviderMethods, defaultValues];
};

type OrderWithdrawalFormMetaType = {
    formName: string;
    messages: {
        error: string;
        success: string;
    };
    fields: {
        [key in keyof OrderWithdrawalFormType]: {
            name: key;
            label: string;
            errorMessage: string | undefined;
        };
    };
};

export const useOrderWithdrawalFormMeta = (
    formProviderMethods: UseFormReturn<OrderWithdrawalFormType>,
): OrderWithdrawalFormMetaType => {
    const { t } = useTranslation();
    const errors = formProviderMethods.formState.errors;

    const formMeta = useMemo(
        () => ({
            formName: 'order-withdrawal-form',
            messages: {
                error: t('The withdrawal request could not be submitted'),
                success: t('Your withdrawal request has been submitted'),
            },
            fields: {
                firstName: {
                    name: 'firstName' as const,
                    label: t('First name'),
                    errorMessage: errors.firstName?.message,
                },
                lastName: {
                    name: 'lastName' as const,
                    label: t('Last name'),
                    errorMessage: errors.lastName?.message,
                },
                email: {
                    name: 'email' as const,
                    label: t('Email'),
                    errorMessage: errors.email?.message,
                },
                telephone: {
                    name: 'telephone' as const,
                    label: t('Phone number (optional)'),
                    errorMessage: errors.telephone?.message,
                },
                note: {
                    name: 'note' as const,
                    label: t('Note (optional)'),
                    errorMessage: errors.note?.message,
                },
            },
        }),
        [errors, t],
    );

    return formMeta;
};
