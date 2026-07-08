// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeBreadcrumbFragment = (
  { __typename: 'Link' }
  & Pick<Types.TypeLink, 'name' | 'slug'>
);

export const BreadcrumbFragment = gql`
    fragment BreadcrumbFragment on Link {
  __typename
  name
  slug
}
    `;