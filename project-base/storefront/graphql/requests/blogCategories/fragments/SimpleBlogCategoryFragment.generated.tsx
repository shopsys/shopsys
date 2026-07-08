// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeSimpleBlogCategoryFragment = (
  { __typename: 'BlogCategory' }
  & Pick<Types.TypeBlogCategory, 'uuid' | 'name' | 'link'>
  & { parent: Types.Maybe<(
    { __typename?: 'BlogCategory' }
    & Pick<Types.TypeBlogCategory, 'name'>
  )> }
);

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