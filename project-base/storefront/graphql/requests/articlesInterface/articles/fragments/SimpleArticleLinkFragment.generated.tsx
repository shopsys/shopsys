// @ts-nocheck
import * as Types from '../../../../types';

import gql from 'graphql-tag';
export type TypeSimpleArticleLinkFragment = { __typename: 'ArticleLink', uuid: string, name: string, url: string, placement: string, external: boolean };

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