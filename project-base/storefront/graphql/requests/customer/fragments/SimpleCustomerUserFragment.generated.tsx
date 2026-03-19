// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CustomerUserRoleGroupFragment } from './CustomerUserRoleGroupGragment.generated';
export type TypeSimpleCustomerUserFragment_CompanyCustomerUser_ = { __typename: 'CompanyCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, roles: Array<Types.TypeCustomerUserRoleEnum>, telephoneData: { __typename?: 'PhoneData', prefix: string | null, countryCode: string | null, number: string } | null, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string } };

export type TypeSimpleCustomerUserFragment_CurrentCompanyCustomerUser_ = { __typename: 'CurrentCompanyCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, roles: Array<Types.TypeCustomerUserRoleEnum>, telephoneData: { __typename?: 'PhoneData', prefix: string | null, countryCode: string | null, number: string } | null, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string } };

export type TypeSimpleCustomerUserFragment_CurrentRegularCustomerUser_ = { __typename: 'CurrentRegularCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, roles: Array<Types.TypeCustomerUserRoleEnum>, telephoneData: { __typename?: 'PhoneData', prefix: string | null, countryCode: string | null, number: string } | null, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string } };

export type TypeSimpleCustomerUserFragment_RegularCustomerUser_ = { __typename: 'RegularCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, roles: Array<Types.TypeCustomerUserRoleEnum>, telephoneData: { __typename?: 'PhoneData', prefix: string | null, countryCode: string | null, number: string } | null, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string } };

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