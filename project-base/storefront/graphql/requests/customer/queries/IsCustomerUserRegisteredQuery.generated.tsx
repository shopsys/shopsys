// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeIsCustomerUserRegisteredQueryVariables = Types.Exact<{
  email: Types.Scalars['String']['input'];
}>;


export type TypeIsCustomerUserRegisteredQuery = (
  { __typename?: 'Query' }
  & Pick<Types.TypeQuery, 'isCustomerUserRegistered'>
);


export const IsCustomerUserRegisteredQueryDocument = gql`
    query IsCustomerUserRegisteredQuery($email: String!) {
  isCustomerUserRegistered(email: $email)
}
    `;

export function useIsCustomerUserRegisteredQuery(options: Omit<Urql.UseQueryArgs<TypeIsCustomerUserRegisteredQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeIsCustomerUserRegisteredQuery, TypeIsCustomerUserRegisteredQueryVariables>({ query: IsCustomerUserRegisteredQueryDocument, ...options });
};