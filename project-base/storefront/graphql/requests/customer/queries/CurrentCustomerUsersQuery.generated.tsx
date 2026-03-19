// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimpleCustomerUserFragment } from '../fragments/SimpleCustomerUserFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeCurrentCustomerUsersQueryVariables = Types.Exact<{ [key: string]: never; }>;


export type TypeCurrentCustomerUsersQuery = { __typename?: 'Query', customerUsers: Array<{ __typename: 'CompanyCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, roles: Array<Types.TypeCustomerUserRoleEnum>, telephoneData: { __typename?: 'PhoneData', prefix: string | null, countryCode: string | null, number: string } | null, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string } } | { __typename: 'CurrentCompanyCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, roles: Array<Types.TypeCustomerUserRoleEnum>, telephoneData: { __typename?: 'PhoneData', prefix: string | null, countryCode: string | null, number: string } | null, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string } } | { __typename: 'CurrentRegularCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, roles: Array<Types.TypeCustomerUserRoleEnum>, telephoneData: { __typename?: 'PhoneData', prefix: string | null, countryCode: string | null, number: string } | null, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string } } | { __typename: 'RegularCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, roles: Array<Types.TypeCustomerUserRoleEnum>, telephoneData: { __typename?: 'PhoneData', prefix: string | null, countryCode: string | null, number: string } | null, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string } }> };


export const CurrentCustomerUsersQueryDocument = gql`
    query CurrentCustomerUsersQuery {
  customerUsers {
    ...SimpleCustomerUserFragment
  }
}
    ${SimpleCustomerUserFragment}`;

export function useCurrentCustomerUsersQuery(options?: Omit<Urql.UseQueryArgs<TypeCurrentCustomerUsersQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeCurrentCustomerUsersQuery, TypeCurrentCustomerUsersQueryVariables>({ query: CurrentCustomerUsersQueryDocument, ...options });
};