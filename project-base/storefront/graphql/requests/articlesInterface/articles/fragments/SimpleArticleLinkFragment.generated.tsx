// @ts-nocheck
import * as Types from '../../../../types';

import gql from 'graphql-tag';
export type TypeSimpleArticleLinkFragment = (
  { __typename: 'ArticleLink' }
  & Pick<Types.TypeArticleLink, 'uuid' | 'name' | 'url' | 'placement' | 'external'>
);

export const SimpleArticleLinkFragment = gql`
    fragment SimpleArticleLinkFragment on ArticleLink {
  __typename
  uuid
  name
  url
  placement
  external
}
    `;