import { yupResolver } from '@hookform/resolvers/yup';
import { useShopsysForm } from 'hooks/forms/UseShopsysForm';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { UseFormReturn } from 'react-hook-form';
import { AutocompleteSearchFormType } from 'types/form';
import * as Yup from 'yup';

export const useAutocompleteSearchForm = (): [
    UseFormReturn<AutocompleteSearchFormType>,
    AutocompleteSearchFormType,
] => {
    const resolver = yupResolver(
        Yup.object().shape({
            autocompleteSearchQuery: Yup.string().required().min(3),
        }),
    );
    const defaultValues = { autocompleteSearchQuery: '' };

    return [useShopsysForm(resolver, defaultValues), defaultValues];
};

type SearchFormMetaType = {
    formName: string;
    fields: {
        [key in keyof AutocompleteSearchFormType]: {
            name: key;
            label: string;
            errorMessage: string | undefined;
        };
    };
};

export const useAutocompleteSearchFormMeta = (
    formProviderMethods: UseFormReturn<AutocompleteSearchFormType>,
): SearchFormMetaType => {
    const t = useTypedTranslationFunction();

    const formMeta = {
        formName: 'search-query-form',
        fields: {
            autocompleteSearchQuery: {
                name: 'autocompleteSearchQuery' as const,
                label: t("Type what you're looking for"),
                errorMessage: formProviderMethods.formState.errors.autocompleteSearchQuery?.message,
            },
        },
    };

    return formMeta;
};
