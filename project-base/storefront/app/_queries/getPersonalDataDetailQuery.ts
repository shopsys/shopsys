import { createQuery } from 'app/_urql/urql-dto';
import {
    PersonalDataDetailQueryDocument,
    TypePersonalDataDetailQuery,
    TypePersonalDataDetailQueryVariables,
} from 'graphql/requests/personalData/queries/PersonalDataDetailQuery.ssr';
import 'server-only';

export const getPersonalDataDetailQuery = async (variables: TypePersonalDataDetailQueryVariables) => {
    const result = await createQuery<TypePersonalDataDetailQuery, TypePersonalDataDetailQueryVariables>(
        PersonalDataDetailQueryDocument,
        variables,
    );

    return { data: result.data, error: result.error };
};
