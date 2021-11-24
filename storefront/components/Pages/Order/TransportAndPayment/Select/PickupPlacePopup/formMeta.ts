import * as Yup from 'yup';
import { UseFormReturn } from 'react-hook-form';
import { useShopsysForm } from 'hooks/forms/UseShopsysForm';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { yupResolver } from '@hookform/resolvers/yup';

export type PickupPlaceFormType = {
    pickupPlace: string;
};

export const usePickupPlaceForm = (): [UseFormReturn<PickupPlaceFormType>, PickupPlaceFormType] => {
    const resolver = yupResolver(
        Yup.object().shape({
            pickupPlace: Yup.string().required(),
        }),
    );
    const defaultValues = { pickupPlace: '' };

    return [useShopsysForm(resolver, defaultValues), defaultValues];
};

type PickupPlaceFormMetaType = {
    formName: string;
    fields: {
        [key in keyof PickupPlaceFormType]: {
            name: key;
            label: string;
            errorMessage: string | undefined;
        };
    };
};

export const usePickupPlaceFormMeta = (
    formProviderMethods: UseFormReturn<PickupPlaceFormType>,
): PickupPlaceFormMetaType => {
    const t = useTypedTranslationFunction();

    const formMeta = {
        formName: 'pickup-place-form',
        fields: {
            pickupPlace: {
                name: 'pickupPlace' as const,
                label: t('Choose the store where you are going to pick up your order'),
                errorMessage: formProviderMethods.formState.errors.pickupPlace?.message,
            },
        },
    };

    return formMeta;
};
