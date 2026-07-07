// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CustomerUserRoleGroupFragment } from './CustomerUserRoleGroupGragment.generated';
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

export type TypeSimpleCustomerUserFragment_CompanyCustomerUser = { __typename: 'CompanyCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, roles: Array<Types.TypeCustomerUserRoleEnum>, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string } };

export type TypeSimpleCustomerUserFragment_CurrentCompanyCustomerUser = { __typename: 'CurrentCompanyCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, roles: Array<Types.TypeCustomerUserRoleEnum>, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string } };

export type TypeSimpleCustomerUserFragment_CurrentRegularCustomerUser = { __typename: 'CurrentRegularCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, roles: Array<Types.TypeCustomerUserRoleEnum>, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string } };

export type TypeSimpleCustomerUserFragment_RegularCustomerUser = { __typename: 'RegularCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, roles: Array<Types.TypeCustomerUserRoleEnum>, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string } };

export type TypeSimpleCustomerUserFragment =
  | TypeSimpleCustomerUserFragment_CompanyCustomerUser
  | TypeSimpleCustomerUserFragment_CurrentCompanyCustomerUser
  | TypeSimpleCustomerUserFragment_CurrentRegularCustomerUser
  | TypeSimpleCustomerUserFragment_RegularCustomerUser
;

export const SimpleCustomerUserFragment = gql`
    fragment SimpleCustomerUserFragment on BaseCustomerUser {
  __typename
  uuid
  firstName
  lastName
  email
  telephone
  telephoneData {
    prefix
    countryCode
    number
  }
  roles
  roleGroup {
    ...CustomerUserRoleGroupFragment
  }
}
    ${CustomerUserRoleGroupFragment}`;