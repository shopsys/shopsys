// @ts-nocheck
import * as Types from '../../../../types';

import gql from 'graphql-tag';
export type TypeSimpleArticleSiteFragment = (
  { __typename: 'ArticleSite' }
  & Pick<Types.TypeArticleSite, 'uuid' | 'name' | 'slug' | 'placement' | 'external'>
);

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