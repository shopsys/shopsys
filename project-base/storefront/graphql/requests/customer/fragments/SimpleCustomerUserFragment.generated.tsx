// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CustomerUserRoleGroupFragment } from './CustomerUserRoleGroupGragment.generated';
export type TypeSimpleCustomerUserFragment_CompanyCustomerUser_ = (
  { __typename: 'CompanyCustomerUser' }
  & Pick<Types.TypeCompanyCustomerUser, 'uuid' | 'firstName' | 'lastName' | 'email' | 'telephone' | 'roles'>
  & { telephoneData: Types.Maybe<(
    { __typename?: 'PhoneData' }
    & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
  )>, roleGroup: (
    { __typename: 'CustomerUserRoleGroup' }
    & Pick<Types.TypeCustomerUserRoleGroup, 'uuid' | 'name'>
  ) }
);

export type TypeSimpleCustomerUserFragment_CurrentCompanyCustomerUser_ = (
  { __typename: 'CurrentCompanyCustomerUser' }
  & Pick<Types.TypeCurrentCompanyCustomerUser, 'uuid' | 'firstName' | 'lastName' | 'email' | 'telephone' | 'roles'>
  & { telephoneData: Types.Maybe<(
    { __typename?: 'PhoneData' }
    & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
  )>, roleGroup: (
    { __typename: 'CustomerUserRoleGroup' }
    & Pick<Types.TypeCustomerUserRoleGroup, 'uuid' | 'name'>
  ) }
);

export type TypeSimpleCustomerUserFragment_CurrentRegularCustomerUser_ = (
  { __typename: 'CurrentRegularCustomerUser' }
  & Pick<Types.TypeCurrentRegularCustomerUser, 'uuid' | 'firstName' | 'lastName' | 'email' | 'telephone' | 'roles'>
  & { telephoneData: Types.Maybe<(
    { __typename?: 'PhoneData' }
    & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
  )>, roleGroup: (
    { __typename: 'CustomerUserRoleGroup' }
    & Pick<Types.TypeCustomerUserRoleGroup, 'uuid' | 'name'>
  ) }
);

export type TypeSimpleCustomerUserFragment_RegularCustomerUser_ = (
  { __typename: 'RegularCustomerUser' }
  & Pick<Types.TypeRegularCustomerUser, 'uuid' | 'firstName' | 'lastName' | 'email' | 'telephone' | 'roles'>
  & { telephoneData: Types.Maybe<(
    { __typename?: 'PhoneData' }
    & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
  )>, roleGroup: (
    { __typename: 'CustomerUserRoleGroup' }
    & Pick<Types.TypeCustomerUserRoleGroup, 'uuid' | 'name'>
  ) }
);

export type TypeSimpleCustomerUserFragment = TypeSimpleCustomerUserFragment_CompanyCustomerUser_ | TypeSimpleCustomerUserFragment_CurrentCompanyCustomerUser_ | TypeSimpleCustomerUserFragment_CurrentRegularCustomerUser_ | TypeSimpleCustomerUserFragment_RegularCustomerUser_;

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