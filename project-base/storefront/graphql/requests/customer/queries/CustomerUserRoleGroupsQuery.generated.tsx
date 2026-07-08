// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CustomerUserRoleGroupFragment } from '../fragments/CustomerUserRoleGroupGragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeCustomerUserRoleGroupsQueryVariables = Types.Exact<{ [key: string]: never; }>;


export type TypeCustomerUserRoleGroupsQuery = (
  { __typename?: 'Query' }
  & { customerUserRoleGroups: Array<(
    { __typename: 'CustomerUserRoleGroup' }
    & Pick<Types.TypeCustomerUserRoleGroup, 'uuid' | 'name'>
  )> }
);


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