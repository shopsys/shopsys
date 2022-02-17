import * as Yup from 'yup';
import { TransportAndPaymentFormType } from 'types/form';
import { UseFormReturn } from 'react-hook-form';
import { useShopsysForm } from 'hooks/forms/UseShopsysForm';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { yupResolver } from '@hookform/resolvers/yup';

export const useTransportAndPaymentForm = (): [
    UseFormReturn<TransportAndPaymentFormType>,
    TransportAndPaymentFormType,
] => {
    const t = useTypedTranslationFunction();
    const {
        transport,
        cartInput: { transport: transportInput, payment: paymentInput },
    } = useShopsysSelector((state) => state.cart);

    const resolver = yupResolver(
        Yup.object().shape({
            transport: Yup.string()
                .required(t('Please select transport'))
                .test(
                    'is-transport-correctly-selected',
                    t('Please select transport with a personal pickup place'),
                    () =>
                        transport?.isPersonalPickup === true ? transportInput?.pickupPlaceIdentifier !== null : true,
                ),
            payment: Yup.string().required(t('Please select payment')),
            goPaySwift: Yup.string().required(t('Please hoose GoPay payment type')),
        }),
    );
    const defaultValues = {
        transport: transportInput === null ? null : transportInput.uuid,
        payment: paymentInput === null ? null : paymentInput.uuid,
        goPaySwift: paymentInput?.goPayBankSwift ?? null,
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
            goPaySwift: {
                name: 'goPaySwift' as const,
                label: t('Choose GoPay payment type'),
                errorMessage: formProviderMethods.formState.errors.payment?.message,
            },
        },
    };

    return formMeta;
};
