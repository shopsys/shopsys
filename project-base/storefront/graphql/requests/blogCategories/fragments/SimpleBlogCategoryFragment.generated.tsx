// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeSimpleBlogCategoryFragment = { __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: { __typename?: 'BlogCategory', name: string } | null };

export const SimpleBlogCategoryFragment = gql`
    fragment SimpleBlogCategoryFragment on BlogCategory {
  __typename
  uuid
  name
  link
  parent {
    name
  }
}
    `;