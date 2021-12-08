import * as Yup from 'yup';
import { PromoCodeFormType } from 'types/form';
import { UseFormReturn } from 'react-hook-form';
import { useShopsysForm } from 'hooks/forms/UseShopsysForm';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { yupResolver } from '@hookform/resolvers/yup';

export const usePromoCodeForm = (): [UseFormReturn<PromoCodeFormType>, PromoCodeFormType] => {
    const t = useTypedTranslationFunction();
    const { promoCode } = useShopsysSelector((state) => state.cart.cartInput);

    const resolver = yupResolver(
        Yup.object().shape({
            promoCode: Yup.string().required(t('This field is required')),
        }),
    );
    const defaultValues = { promoCode: promoCode === null ? '' : promoCode };

    return [useShopsysForm(resolver, defaultValues), defaultValues];
};

type PromoCodeFormMeta = {
    formName: string;
    messages: {
        success: string;
    };
    fields: {
        [key in keyof PromoCodeFormType]: {
            name: key;
            label: string;
            errorMessage: string | undefined;
        };
    };
};

export const usePromoCodeFormMeta = (formProviderMethods: UseFormReturn<PromoCodeFormType>): PromoCodeFormMeta => {
    const t = useTypedTranslationFunction();

    const formMeta = {
        formName: 'promo-code-form',
        messages: {
            success: t('Promo code was added to the order.'),
        },
        fields: {
            promoCode: {
                name: 'promoCode' as const,
                label: t('Coupon'),
                errorMessage: formProviderMethods.formState.errors.promoCode?.message,
            },
        },
    };

    return formMeta;
};
