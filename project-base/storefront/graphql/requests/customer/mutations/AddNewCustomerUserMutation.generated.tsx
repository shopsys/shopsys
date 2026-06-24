// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimpleCustomerUserFragment } from '../fragments/SimpleCustomerUserFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeAddNewCustomerUserDataInput = {
  /** Customer user email. */
  email: string;
  /** Customer user first name */
  firstName: string;
  /** Customer user last name */
  lastName: string;
  /** Whether customer user should receive newsletters or not */
  newsletterSubscription: boolean;
  /** Customer user role group uuid. */
  roleGroupUuid: string;
  /** The customer's telephone */
  telephone: TypePhoneDataInput;
};

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

/** Represents phone number input */
export type TypePhoneDataInput = {
  /** Phone prefix country code in ISO 3166-1 alpha-2 */
  countryCode: string;
  /** Phone number without prefix */
  number: string;
  /** Phone prefix (eg. +420) */
  prefix: string;
};

export type TypeAddNewCustomerUserMutationVariables = Exact<{
  input: Types.TypeAddNewCustomerUserDataInput;
}>;


export type TypeAddNewCustomerUserMutation = { AddNewCustomerUser:
    | { __typename: 'CompanyCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, roles: Array<Types.TypeCustomerUserRoleEnum>, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string } }
    | { __typename: 'CurrentCompanyCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, roles: Array<Types.TypeCustomerUserRoleEnum>, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string } }
    | { __typename: 'CurrentRegularCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, roles: Array<Types.TypeCustomerUserRoleEnum>, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string } }
    | { __typename: 'RegularCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, roles: Array<Types.TypeCustomerUserRoleEnum>, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string } }
   };


export const AddNewCustomerUserMutationDocument = gql`
    mutation AddNewCustomerUserMutation($input: AddNewCustomerUserDataInput!) {
  AddNewCustomerUser(input: $input) {
    ...SimpleCustomerUserFragment
  }
}
    ${SimpleCustomerUserFragment}`;

export function useAddNewCustomerUserMutation() {
  return Urql.useMutation<TypeAddNewCustomerUserMutation, TypeAddNewCustomerUserMutationVariables>(AddNewCustomerUserMutationDocument);
};