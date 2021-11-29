import * as Yup from 'yup';
import { UseFormReturn } from 'react-hook-form';
import { useShopsysForm } from 'hooks/forms/UseShopsysForm';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { yupResolver } from '@hookform/resolvers/yup';

export type SearchFormType = {
    searchQuery: string;
};

export const useSearchForm = (): [UseFormReturn<SearchFormType>, SearchFormType] => {
    const resolver = yupResolver(
        Yup.object().shape({
            searchQuery: Yup.string().required().min(3),
        }),
    );
    const defaultValues = { searchQuery: '' };

    return [useShopsysForm(resolver, defaultValues), defaultValues];
};

type SearchFormMetaType = {
    formName: string;
    fields: {
        [key in keyof SearchFormType]: {
            name: key;
            label: string;
            errorMessage: string | undefined;
        };
    };
};

export const useSearchFormMeta = (formProviderMethods: UseFormReturn<SearchFormType>): SearchFormMetaType => {
    const t = useTypedTranslationFunction();

    const formMeta = {
        formName: 'search-query-form',
        fields: {
            searchQuery: {
                name: 'searchQuery' as const,
                label: t("Type what you're looking for"),
                errorMessage: formProviderMethods.formState.errors.searchQuery?.message,
            },
        },
    };

    return formMeta;
};
