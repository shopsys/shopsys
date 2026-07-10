import { UseFormReturn } from 'react-hook-form';
import { ApplyCodeFormType } from 'types/form';
import { FormMeta } from 'types/formMeta';
import { createFields } from 'utils/forms/createFields';
import { useFormWrapper } from 'utils/forms/useFormWrapper';
import { yupResolver } from 'utils/forms/yupResolver';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import * as Yup from 'yup';

export const useApplyCodeForm = (): [UseFormReturn<ApplyCodeFormType>, ApplyCodeFormType] => {
    const { t } = useTranslation();

    const resolver = yupResolver<ApplyCodeFormType>(
        Yup.object().shape<Record<keyof ApplyCodeFormType, any>>({
            code: Yup.string().required(t('This field is required')),
        }),
    );
    const defaultValues = { code: '' };

    return [useFormWrapper(resolver, defaultValues), defaultValues];
};

export const useApplyCodeFormMeta = (): FormMeta<ApplyCodeFormType> => {
    const { t } = useTranslation();
    return {
        formName: 'apply-code-form',
        messages: {},
        fields: createFields<ApplyCodeFormType>({
            code: t('Code'),
        }),
    };
};
