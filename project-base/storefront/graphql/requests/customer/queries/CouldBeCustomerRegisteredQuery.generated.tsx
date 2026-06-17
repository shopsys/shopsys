// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeCouldBeCustomerRegisteredQueryVariables = Exact<{
  email: string;
  companyNumber?: string | null | undefined;
}>;


export type TypeCouldBeCustomerRegisteredQuery = { couldBeCustomerRegisteredQuery: boolean };


export const CouldBeCustomerRegisteredQueryDocument = gql`
    query CouldBeCustomerRegisteredQuery($email: String!, $companyNumber: String) {
  couldBeCustomerRegisteredQuery(email: $email, companyNumber: $companyNumber)
}
    `;

export function useCouldBeCustomerRegisteredQuery(options: Omit<Urql.UseQueryArgs<TypeCouldBeCustomerRegisteredQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeCouldBeCustomerRegisteredQuery, TypeCouldBeCustomerRegisteredQueryVariables>({ query: CouldBeCustomerRegisteredQueryDocument, ...options });
};