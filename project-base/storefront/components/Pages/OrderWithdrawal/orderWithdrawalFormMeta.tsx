import { yupResolver } from '@hookform/resolvers/yup';
import {
    validateEmail,
    validateFirstName,
    validateLastName,
    validateTelephone,
} from 'components/Forms/validationRules';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { TypeOrderWithdrawalDataFragment } from 'graphql/requests/orders/fragments/OrderWithdrawalDataFragment.generated';
import { UseFormReturn } from 'react-hook-form';
import { OrderWithdrawalFormType } from 'types/form';
import { FormMeta } from 'types/formMeta';
import { createFields } from 'utils/forms/createFields';
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

export const useOrderWithdrawalFormMeta = (): FormMeta<OrderWithdrawalFormType, { error: string; success: string }> => {
    const { t } = useTranslation();
    return {
        formName: 'order-withdrawal-form',
        messages: {
            error: t('The withdrawal request could not be submitted'),
            success: t('Your withdrawal request has been submitted'),
        },
        fields: createFields<OrderWithdrawalFormType>({
            firstName: t('First name'),
            lastName: t('Last name'),
            email: t('Email'),
            telephone: t('Phone number (optional)'),
            note: t('Note (optional)'),
        }),
    };
};
