import { yupResolver } from '@hookform/resolvers/yup';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';
import { UseFormReturn } from 'react-hook-form';
import { PromoCodeFormType } from 'types/form';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { useShopsysForm } from 'utils/forms/useShopsysForm';
import * as Yup from 'yup';

export const usePromoCodeForm = (): [UseFormReturn<PromoCodeFormType>, PromoCodeFormType] => {
    const { t } = useTranslation();
    const { promoCodes } = useCurrentCart();

    const resolver = yupResolver(
        Yup.object().shape<Record<keyof PromoCodeFormType, any>>({
            promoCode: Yup.string().required(t('This field is required')),
        }),
    );
    const defaultValues = { promoCode: promoCodes[0]?.code ?? '' };

    return [useShopsysForm(resolver, defaultValues), defaultValues];
};

type PromoCodeFormMetaType = {
    formName: string;
    fields: {
        [key in keyof PromoCodeFormType]: {
            name: key;
            label: string | JSX.Element;
            errorMessage: string | undefined;
        };
    };
};

export const usePromoCodeFormMeta = (formProviderMethods: UseFormReturn<PromoCodeFormType>): PromoCodeFormMetaType => {
    const { t } = useTranslation();
    const errors = formProviderMethods.formState.errors;

    const formMeta = useMemo(
        () => ({
            formName: 'promoCode-form',
            fields: {
                promoCode: {
                    name: 'promoCode' as const,
                    label: t('Coupon'),
                    errorMessage: errors.promoCode?.message,
                },
            },
        }),
        [t, errors.promoCode?.message],
    );

    return formMeta;
};
