// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeBreadcrumbFragment = { __typename: 'Link', name: string, slug: string };

export const BreadcrumbFragment = gql`
    fragment BreadcrumbFragment on Link {
  __typename
  name
  slug
}
    `;