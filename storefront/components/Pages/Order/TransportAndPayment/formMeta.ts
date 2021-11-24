import * as Yup from 'yup';
import { UseFormReturn } from 'react-hook-form';
import { useShopsysForm } from 'hooks/forms/UseShopsysForm';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { yupResolver } from '@hookform/resolvers/yup';

export type TransportAndPaymentFormType = {
    transport: string | null;
    payment: string | null;
};

export const useTransportAndPaymentForm = (): [
    UseFormReturn<TransportAndPaymentFormType>,
    TransportAndPaymentFormType,
] => {
    const t = useTypedTranslationFunction();
    const { transport, payment } = useShopsysSelector((state) => state.cartInput);
    const transportObject = useShopsysSelector((state) => state.user.transport);

    const resolver = yupResolver(
        Yup.object().shape({
            transport: Yup.string()
                .required(t('Please select transport'))
                .test(
                    'is-transport-correctly-selected',
                    t('Please select transport with a personal pickup place'),
                    () =>
                        transportObject?.isPersonalPickup === true ? transport?.pickupPlaceIdentifier !== null : true,
                ),
            payment: Yup.string().required(t('Please select payment')),
        }),
    );
    const defaultValues = {
        transport: transport === null ? null : transport.uuid,
        payment: payment === null ? null : payment.uuid,
    };

    return [useShopsysForm(resolver, defaultValues), defaultValues];
};

type TransportAndPaymentFormMetaType = {
    formName: string;
    fields: {
        [key in keyof TransportAndPaymentFormType]: {
            name: key;
            label: string;
            errorMessage: string | undefined;
        };
    };
};

export const useTransportAndPaymentFormMeta = (
    formProviderMethods: UseFormReturn<TransportAndPaymentFormType>,
): TransportAndPaymentFormMetaType => {
    const t = useTypedTranslationFunction();

    const formMeta = {
        formName: 'transport-and-payment-form',
        fields: {
            transport: {
                name: 'transport' as const,
                label: t('Choose transport type'),
                errorMessage: formProviderMethods.formState.errors.transport?.message,
            },
            payment: {
                name: 'payment' as const,
                label: t('Choose payment type'),
                errorMessage: formProviderMethods.formState.errors.payment?.message,
            },
        },
    };

    return formMeta;
};
