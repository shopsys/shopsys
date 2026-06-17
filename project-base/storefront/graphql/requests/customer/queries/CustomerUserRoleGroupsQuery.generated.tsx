// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CustomerUserRoleGroupFragment } from '../fragments/CustomerUserRoleGroupGragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeCustomerUserRoleGroupsQueryVariables = Exact<{ [key: string]: never; }>;


export type TypeCustomerUserRoleGroupsQuery = { customerUserRoleGroups: Array<{ __typename: 'CustomerUserRoleGroup', uuid: string, name: string }> };


export const CustomerUserRoleGroupsQueryDocument = gql`
    query CustomerUserRoleGroupsQuery {
  customerUserRoleGroups {
    ...CustomerUserRoleGroupFragment
  }
}
    ${CustomerUserRoleGroupFragment}`;

export function useCustomerUserRoleGroupsQuery(options?: Omit<Urql.UseQueryArgs<TypeCustomerUserRoleGroupsQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeCustomerUserRoleGroupsQuery, TypeCustomerUserRoleGroupsQueryVariables>({ query: CustomerUserRoleGroupsQueryDocument, ...options });
};