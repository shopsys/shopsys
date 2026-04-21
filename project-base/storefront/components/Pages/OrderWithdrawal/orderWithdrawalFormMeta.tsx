import { yupResolver } from '@hookform/resolvers/yup';
import {
    validateEmail,
    validateFirstName,
    validateLastName,
    validateTelephone,
    validateTelephonePrefix,
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
            telephonePrefix: Yup.string().when('telephone', {
                is: (telephone: string) => telephone.length > 0,
                then: () => validateTelephonePrefix(t),
                otherwise: (schema) => schema,
            }),
            telephonePrefixCountryCode: Yup.string(),
            telephone: validateTelephone(t),
            note: Yup.string().optional(),
        }),
    );

    const defaultValues: OrderWithdrawalFormType = {
        firstName: order?.firstName ?? user?.firstName ?? '',
        lastName: order?.lastName ?? user?.lastName ?? '',
        email: order?.email ?? user?.email ?? '',
        telephonePrefix: order?.telephoneData.prefix ?? user?.telephonePrefix ?? '',
        telephonePrefixCountryCode: order?.telephoneData.countryCode ?? user?.telephonePrefixCountryCode ?? '',
        telephone: order?.telephoneData.number ?? user?.telephoneNumber ?? '',
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
            telephonePrefix: t('Phone prefix'),
            telephonePrefixCountryCode: t('Country code'),
            telephone: t('Phone number (optional)'),
            note: t('Note (optional)'),
        }),
    };
};
