// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeSalesRepresentativeFragment = { __typename: 'SalesRepresentative', email: string | null, firstName: string | null, lastName: string | null, telephone: string | null, uuid: string, image: { __typename?: 'Image', url: string, name: string | null } | null };

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
  uuid
}
    `;