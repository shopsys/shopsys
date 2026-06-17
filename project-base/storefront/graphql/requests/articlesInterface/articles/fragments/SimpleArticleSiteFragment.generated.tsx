// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../../types';

import gql from 'graphql-tag';
export type TypeSimpleArticleSiteFragment = { __typename: 'ArticleSite', uuid: string, name: string, slug: string, placement: string, external: boolean };

export const SimpleArticleSiteFragment = gql`
    fragment SimpleArticleSiteFragment on ArticleSite {
  __typename
  uuid
  name
  slug
  placement
  external
}
    `;