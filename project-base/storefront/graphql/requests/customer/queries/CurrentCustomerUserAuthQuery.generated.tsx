// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
/** Available customer user roles */
export type TypeCustomerUserRoleEnum =
  | 'ROLE_API_ALL'
  | 'ROLE_API_CART_AND_ORDER_CREATION'
  | 'ROLE_API_COMPANY_COMPLAINTS_VIEW'
  | 'ROLE_API_COMPANY_ORDERS_VIEW'
  | 'ROLE_API_COMPLAINT_CREATION'
  | 'ROLE_API_CUSTOMER_SEES_PRICES'
  | 'ROLE_API_CUSTOMER_SELF_MANAGE'
  | 'ROLE_API_MANAGE_COMPANY_DATA'
  | 'ROLE_API_MANAGE_CUSTOMERS';

export type TypeCurrentCustomerUserAuthQueryVariables = Exact<{ [key: string]: never; }>;


export type TypeCurrentCustomerUserAuthQuery = { currentCustomerUser:
    | { __typename: 'CurrentCompanyCustomerUser', uuid: string, roles: Array<Types.TypeCustomerUserRoleEnum> }
    | { __typename: 'CurrentRegularCustomerUser', uuid: string, roles: Array<Types.TypeCustomerUserRoleEnum> }
   | null };


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