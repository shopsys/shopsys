// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeSalesRepresentativeFragment = (
  { __typename: 'SalesRepresentative' }
  & Pick<Types.TypeSalesRepresentative, 'email' | 'firstName' | 'lastName' | 'telephone' | 'uuid'>
  & { image: Types.Maybe<(
    { __typename?: 'Image' }
    & Pick<Types.TypeImage, 'url' | 'name'>
  )>, telephoneData: Types.Maybe<(
    { __typename?: 'PhoneData' }
    & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
  )> }
);

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