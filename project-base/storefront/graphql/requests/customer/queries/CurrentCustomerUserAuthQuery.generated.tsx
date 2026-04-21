// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeCurrentCustomerUserAuthQueryVariables = Types.Exact<{ [key: string]: never; }>;


export type TypeCurrentCustomerUserAuthQuery = { __typename?: 'Query', currentCustomerUser: { __typename: 'CurrentCompanyCustomerUser', uuid: string, roles: Array<Types.TypeCustomerUserRoleEnum> } | { __typename: 'CurrentRegularCustomerUser', uuid: string, roles: Array<Types.TypeCustomerUserRoleEnum> } | null };


export const CurrentCustomerUserAuthQueryDocument = gql`
    query CurrentCustomerUserAuthQuery {
  currentCustomerUser {
    __typename
    uuid
    roles
  }
}
    `;

export function useCurrentCustomerUserAuthQuery(options?: Omit<Urql.UseQueryArgs<TypeCurrentCustomerUserAuthQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeCurrentCustomerUserAuthQuery, TypeCurrentCustomerUserAuthQueryVariables>({ query: CurrentCustomerUserAuthQueryDocument, ...options });
};