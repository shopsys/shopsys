// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { BaseCustomerUserFragment } from './BaseCustomerUserFragment.generated';
import { LoginInfoFragment } from './LoginInfoFragment.generated';
export type TypeCurrentCustomerUserFragment_CurrentCompanyCustomerUser_ = { __typename: 'CurrentCompanyCustomerUser', companyName: string | null, companyNumber: string | null, companyTaxNumber: string | null, uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, billingAddressUuid: string, street: string | null, city: string | null, postcode: string | null, newsletterSubscription: boolean, pricingGroup: string, hasPasswordSet: boolean, roles: Array<Types.TypeCustomerUserRoleEnum>, loginInfo: { __typename: 'LoginInfo', externalId: string | null, loginType: Types.TypeLoginTypeEnum }, country: { __typename: 'Country', name: string, code: string } | null, defaultDeliveryAddress: { __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null } | null, deliveryAddresses: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null }>, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string }, salesRepresentative: { __typename: 'SalesRepresentative', email: string | null, firstName: string | null, lastName: string | null, telephone: string | null, uuid: string, image: { __typename?: 'Image', url: string, name: string | null } | null } | null };

export type TypeCurrentCustomerUserFragment_CurrentRegularCustomerUser_ = { __typename: 'CurrentRegularCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, billingAddressUuid: string, street: string | null, city: string | null, postcode: string | null, newsletterSubscription: boolean, pricingGroup: string, hasPasswordSet: boolean, roles: Array<Types.TypeCustomerUserRoleEnum>, loginInfo: { __typename: 'LoginInfo', externalId: string | null, loginType: Types.TypeLoginTypeEnum }, country: { __typename: 'Country', name: string, code: string } | null, defaultDeliveryAddress: { __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null } | null, deliveryAddresses: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null }>, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string }, salesRepresentative: { __typename: 'SalesRepresentative', email: string | null, firstName: string | null, lastName: string | null, telephone: string | null, uuid: string, image: { __typename?: 'Image', url: string, name: string | null } | null } | null };

export type TypeCurrentCustomerUserFragment = TypeCurrentCustomerUserFragment_CurrentCompanyCustomerUser_ | TypeCurrentCustomerUserFragment_CurrentRegularCustomerUser_;

export const CurrentCustomerUserFragment = gql`
    fragment CurrentCustomerUserFragment on CurrentCustomerUser {
  ...BaseCustomerUserFragment
  ... on CurrentCompanyCustomerUser {
    companyName
    companyNumber
    companyTaxNumber
  }
  loginInfo {
    ...LoginInfoFragment
  }
}
    ${BaseCustomerUserFragment}
${LoginInfoFragment}`;