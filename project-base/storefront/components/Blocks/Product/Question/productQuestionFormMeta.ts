import { validateEmail } from 'components/Forms/validationRules';
import { UseFormReturn } from 'react-hook-form';
import { ProductQuestionFormType } from 'types/form';
import { FormMeta } from 'types/formMeta';
import { createFields } from 'utils/forms/createFields';
import { useFormWrapper } from 'utils/forms/useFormWrapper';
import { yupResolver } from 'utils/forms/yupResolver';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import * as Yup from 'yup';

export const useProductQuestionForm = (
    defaultValues: ProductQuestionFormType,
): [UseFormReturn<ProductQuestionFormType>, ProductQuestionFormType | undefined] => {
    const { t } = useTranslation();

    const resolver = yupResolver<ProductQuestionFormType>(
        Yup.object().shape<Record<keyof ProductQuestionFormType, any>>({
            customerName: Yup.string().required(t('Please enter your name')),
            email: validateEmail(t),
            question: Yup.string().required(t('Please enter your question')),
            productUuid: Yup.string().required(),
        }),
    );

    return [useFormWrapper(resolver, defaultValues), defaultValues];
};

export const useProductQuestionFormMeta = (): FormMeta<ProductQuestionFormType, { error: string }> => {
    const { t } = useTranslation();

    return {
        formName: 'product-question-form',
        messages: {
            error: t('An error occurred while sending your question'),
        },
        fields: createFields<ProductQuestionFormType>({
            customerName: t('Your name'),
            email: t('Your email'),
            question: t('Your question'),
            productUuid: t('Product'),
        }),
    };
};
