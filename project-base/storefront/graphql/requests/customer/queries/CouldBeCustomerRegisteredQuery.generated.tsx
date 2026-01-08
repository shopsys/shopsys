// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeCouldBeCustomerRegisteredQueryVariables = Types.Exact<{
  email: Types.Scalars['String']['input'];
  companyNumber?: Types.InputMaybe<Types.Scalars['String']['input']>;
}>;


export type TypeCouldBeCustomerRegisteredQuery = { __typename?: 'Query', couldBeCustomerRegisteredQuery: boolean };


export const CouldBeCustomerRegisteredQueryDocument = gql`
    query CouldBeCustomerRegisteredQuery($email: String!, $companyNumber: String) {
  couldBeCustomerRegisteredQuery(email: $email, companyNumber: $companyNumber)
}
    `;

export function useCouldBeCustomerRegisteredQuery(options: Omit<Urql.UseQueryArgs<TypeCouldBeCustomerRegisteredQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeCouldBeCustomerRegisteredQuery, TypeCouldBeCustomerRegisteredQueryVariables>({ query: CouldBeCustomerRegisteredQueryDocument, ...options });
};