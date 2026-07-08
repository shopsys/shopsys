// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimpleCustomerUserFragment } from '../fragments/SimpleCustomerUserFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeAddNewCustomerUserMutationVariables = Types.Exact<{
  input: Types.TypeAddNewCustomerUserDataInput;
}>;


export type TypeAddNewCustomerUserMutation = (
  { __typename?: 'Mutation' }
  & { AddNewCustomerUser: (
    { __typename: 'CompanyCustomerUser' }
    & Pick<Types.TypeCompanyCustomerUser, 'uuid' | 'firstName' | 'lastName' | 'email' | 'telephone' | 'roles'>
    & { telephoneData: Types.Maybe<(
      { __typename?: 'PhoneData' }
      & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
    )>, roleGroup: (
      { __typename: 'CustomerUserRoleGroup' }
      & Pick<Types.TypeCustomerUserRoleGroup, 'uuid' | 'name'>
    ) }
  ) | (
    { __typename: 'CurrentCompanyCustomerUser' }
    & Pick<Types.TypeCurrentCompanyCustomerUser, 'uuid' | 'firstName' | 'lastName' | 'email' | 'telephone' | 'roles'>
    & { telephoneData: Types.Maybe<(
      { __typename?: 'PhoneData' }
      & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
    )>, roleGroup: (
      { __typename: 'CustomerUserRoleGroup' }
      & Pick<Types.TypeCustomerUserRoleGroup, 'uuid' | 'name'>
    ) }
  ) | (
    { __typename: 'CurrentRegularCustomerUser' }
    & Pick<Types.TypeCurrentRegularCustomerUser, 'uuid' | 'firstName' | 'lastName' | 'email' | 'telephone' | 'roles'>
    & { telephoneData: Types.Maybe<(
      { __typename?: 'PhoneData' }
      & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
    )>, roleGroup: (
      { __typename: 'CustomerUserRoleGroup' }
      & Pick<Types.TypeCustomerUserRoleGroup, 'uuid' | 'name'>
    ) }
  ) | (
    { __typename: 'RegularCustomerUser' }
    & Pick<Types.TypeRegularCustomerUser, 'uuid' | 'firstName' | 'lastName' | 'email' | 'telephone' | 'roles'>
    & { telephoneData: Types.Maybe<(
      { __typename?: 'PhoneData' }
      & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
    )>, roleGroup: (
      { __typename: 'CustomerUserRoleGroup' }
      & Pick<Types.TypeCustomerUserRoleGroup, 'uuid' | 'name'>
    ) }
  ) }
);


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