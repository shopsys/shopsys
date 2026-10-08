// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeSalesRepresentativeFragment = { __typename: 'SalesRepresentative', email: string | null, firstName: string | null, lastName: string | null, telephone: string | null, uuid: string, image: { url: string, name: string | null } | null, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null };

export const SalesRepresentativeFragment = gql`
    fragment SalesRepresentativeFragment on SalesRepresentative {
  __typename
  email
  firstName
  image {
    url
    name
  }
  lastName
  telephone
  telephoneData {
    prefix
    countryCode
    number
  }
  uuid
}
    `;