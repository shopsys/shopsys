'use server';

import { createQuery } from 'app/_urql/urql-dto';
import {
    CountriesQueryDocument,
    TypeCountriesQuery,
    TypeCountriesQueryVariables,
} from 'graphql/requests/countries/queries/CountriesQuery.ssr';

export const getCountriesQuery = async () => {
    const result = await createQuery<TypeCountriesQuery, TypeCountriesQueryVariables>(CountriesQueryDocument, {});

    return result;
};
