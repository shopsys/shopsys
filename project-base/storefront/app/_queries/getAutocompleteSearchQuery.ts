'use server';

import { createQuery } from 'app/_urql/urql-dto';
import {
    AutocompleteSearchQueryDocument,
    TypeAutocompleteSearchQuery,
    TypeAutocompleteSearchQueryVariables,
} from 'graphql/requests/search/queries/AutocompleteSearchQuery.ssr';

export const getAutocompleteSearchQuery = async (variables: TypeAutocompleteSearchQueryVariables) => {
    const result = await createQuery<TypeAutocompleteSearchQuery, TypeAutocompleteSearchQueryVariables>(
        AutocompleteSearchQueryDocument,
        variables,
    );

    return result.data;
};
