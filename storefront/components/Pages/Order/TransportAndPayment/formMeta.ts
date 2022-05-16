import { yupResolver } from '@hookform/resolvers/yup';
import { useCurrentCart } from 'connectors/cart/Cart';
import { useShopsysForm } from 'hooks/forms/UseShopsysForm';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { UseFormReturn } from 'react-hook-form';
import { TransportAndPaymentFormType } from 'types/form';
import * as Yup from 'yup';

export const useTransportAndPaymentForm = (): [
    UseFormReturn<TransportAndPaymentFormType>,
    TransportAndPaymentFormType,
] => {
    const t = useTypedTranslationFunction();
    const { transport, pickupPlace, payment, paymentGoPayBankSwift } = useCurrentCart();

    const resolver = yupResolver(
        Yup.object().shape({
            transport: Yup.string()
                .nullable()
                .required(t('Please select transport'))
                .test(
                    'is-transport-correctly-selected',
                    t('Please select transport with a personal pickup place'),
                    () => (transport?.isPersonalPickup ? pickupPlace?.identifier !== undefined : true),
                ),
            payment: Yup.string()
                .nullable()
                .test('is-transport-selected-and-payment-is-not-selected', t('Please select payment'), () =>
                    transport !== null ? payment !== null : true,
                ),
            goPaySwift: Yup.string()
                .nullable()
                .test('is-payment-gopay-swift-and-swift-is-not-selected', t('Please select your bank'), () =>
                    payment?.goPayPaymentMethod?.identifier === 'BANK_ACCOUNT' ? paymentGoPayBankSwift !== null : true,
                ),
        }),
    );
    const defaultValues = {
        transport: transport?.uuid ?? null,
        payment: payment?.uuid ?? null,
        goPaySwift: paymentGoPayBankSwift,
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
                label: t('Choose your bank'),
                errorMessage: formProviderMethods.formState.errors.goPaySwift?.message,
            },
        },
    };

    return formMeta;
};
