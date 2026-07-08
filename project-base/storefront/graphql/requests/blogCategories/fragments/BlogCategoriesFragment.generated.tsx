// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimpleBlogCategoryFragment } from './SimpleBlogCategoryFragment.generated';
export type TypeBlogCategoriesFragment = (
  { __typename: 'BlogCategory' }
  & Pick<Types.TypeBlogCategory, 'uuid' | 'name' | 'link'>
  & { children: Array<(
    { __typename: 'BlogCategory' }
    & Pick<Types.TypeBlogCategory, 'uuid' | 'name' | 'link'>
    & { children: Array<(
      { __typename: 'BlogCategory' }
      & Pick<Types.TypeBlogCategory, 'uuid' | 'name' | 'link'>
      & { children: Array<(
        { __typename: 'BlogCategory' }
        & Pick<Types.TypeBlogCategory, 'uuid' | 'name' | 'link'>
        & { children: Array<(
          { __typename: 'BlogCategory' }
          & Pick<Types.TypeBlogCategory, 'uuid' | 'name' | 'link'>
          & { parent: Types.Maybe<(
            { __typename?: 'BlogCategory' }
            & Pick<Types.TypeBlogCategory, 'name'>
          )> }
        )>, parent: Types.Maybe<(
          { __typename?: 'BlogCategory' }
          & Pick<Types.TypeBlogCategory, 'name'>
        )> }
      )>, parent: Types.Maybe<(
        { __typename?: 'BlogCategory' }
        & Pick<Types.TypeBlogCategory, 'name'>
      )> }
    )>, parent: Types.Maybe<(
      { __typename?: 'BlogCategory' }
      & Pick<Types.TypeBlogCategory, 'name'>
    )> }
  )>, parent: Types.Maybe<(
    { __typename?: 'BlogCategory' }
    & Pick<Types.TypeBlogCategory, 'name'>
  )> }
);

export const BlogCategoriesFragment = gql`
    fragment BlogCategoriesFragment on BlogCategory {
  ...SimpleBlogCategoryFragment
  children {
    ...SimpleBlogCategoryFragment
    children {
      ...SimpleBlogCategoryFragment
      children {
        ...SimpleBlogCategoryFragment
        children {
          ...SimpleBlogCategoryFragment
        }
      }
    }
  }
}
    ${SimpleBlogCategoryFragment}`;