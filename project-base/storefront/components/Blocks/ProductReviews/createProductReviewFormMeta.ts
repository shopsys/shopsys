import { VALIDATION_CONSTANTS } from 'components/Forms/validationConstants';
import {
    validateEmail,
    validateFirstName,
    validateLastName,
    validateOptionalImageFiles,
} from 'components/Forms/validationRules';
import { UseFormReturn } from 'react-hook-form';
import { ProductReviewFormType } from 'types/form';
import { FormMeta } from 'types/formMeta';
import { createFields } from 'utils/forms/createFields';
import { useFormWrapper } from 'utils/forms/useFormWrapper';
import { yupResolver } from 'utils/forms/yupResolver';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import * as Yup from 'yup';

export const useCreateProductReviewForm = (
    defaultValues: ProductReviewFormType,
    isUserLoggedIn: boolean,
): [UseFormReturn<ProductReviewFormType>, ProductReviewFormType | undefined] => {
    const { t } = useTranslation();

    const resolver = yupResolver<ProductReviewFormType>(
        Yup.object().shape<Record<keyof ProductReviewFormType, any>>({
            productUuid: Yup.string().required(t('Please select a product variant')),
            rating: Yup.number()
                .min(1, t('Please select 1 to 5 stars'))
                .max(5, t('Please select 1 to 5 stars'))
                .required(t('Please select 1 to 5 stars')),
            text: Yup.string(),
            firstName: validateFirstName(t),
            lastName: validateLastName(t),
            email: isUserLoggedIn ? Yup.string() : validateEmail(t),
            isAnonymous: Yup.boolean(),
            images: validateOptionalImageFiles(t, VALIDATION_CONSTANTS.reviewMaxFilesCount),
        }),
    );

    return [useFormWrapper(resolver, defaultValues), defaultValues];
};

export const useCreateProductReviewFormMeta = (): FormMeta<ProductReviewFormType> => {
    const { t } = useTranslation();

    return {
        formName: 'create-product-review-form',
        messages: {},
        fields: createFields<ProductReviewFormType>({
            productUuid: t('Product variant'),
            rating: t('Rating'),
            text: t('Your experience'),
            firstName: t('First name'),
            lastName: t('Last name'),
            email: t('Your email'),
            isAnonymous: t('Publish anonymously'),
            images: t('Photos'),
        }),
    };
};
