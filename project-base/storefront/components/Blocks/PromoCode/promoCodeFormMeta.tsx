import { UseFormReturn } from 'react-hook-form';
import { PromoCodeFormType } from 'types/form';
import { FormMeta } from 'types/formMeta';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { createFields } from 'utils/forms/createFields';
import { useFormWrapper } from 'utils/forms/useFormWrapper';
import { yupResolver } from 'utils/forms/yupResolver';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import * as Yup from 'yup';

export const usePromoCodeForm = (): [UseFormReturn<PromoCodeFormType>, PromoCodeFormType] => {
    const { t } = useTranslation();
    const { promoCodes } = useCurrentCart();

    const resolver = yupResolver<PromoCodeFormType>(
        Yup.object().shape<Record<keyof PromoCodeFormType, any>>({
            promoCode: Yup.string().required(t('This field is required')),
        }),
    );
    const defaultValues = { promoCode: promoCodes[0]?.code ?? '' };

    return [useFormWrapper(resolver, defaultValues), defaultValues];
};

export const usePromoCodeFormMeta = (): FormMeta<PromoCodeFormType> => {
    const { t } = useTranslation();
    return {
        formName: 'promoCode-form',
        messages: {},
        fields: createFields<PromoCodeFormType>({
            promoCode: t('Coupon'),
        }),
    };
};
