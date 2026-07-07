// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeIsCustomerUserRegisteredQueryVariables = Exact<{
  email: string;
}>;


export type TypeIsCustomerUserRegisteredQuery = { isCustomerUserRegistered: boolean };


export const IsCustomerUserRegisteredQueryDocument = gql`
    query IsCustomerUserRegisteredQuery($email: String!) {
  isCustomerUserRegistered(email: $email)
}
    `;

export function useIsCustomerUserRegisteredQuery(options: Omit<Urql.UseQueryArgs<TypeIsCustomerUserRegisteredQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeIsCustomerUserRegisteredQuery, TypeIsCustomerUserRegisteredQueryVariables>({ query: IsCustomerUserRegisteredQueryDocument, ...options });
};