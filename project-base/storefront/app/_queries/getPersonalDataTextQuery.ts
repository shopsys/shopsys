import { createQuery } from 'app/_urql/urql-dto';
import {
    PersonalDataPageTextQueryDocument,
    TypePersonalDataPageTextQuery,
    TypePersonalDataPageTextQueryVariables,
} from 'graphql/requests/personalData/queries/PersonalDataPageTextQuery.ssr';
import 'server-only';

export const getPersonalDataTextQuery = async () => {
    const result = await createQuery<TypePersonalDataPageTextQuery, TypePersonalDataPageTextQueryVariables>(
        PersonalDataPageTextQueryDocument,
        {},
    );

    return { data: result.data, error: result.error };
};
