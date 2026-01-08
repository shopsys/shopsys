// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimpleBlogCategoryFragment } from './SimpleBlogCategoryFragment.generated';
export type TypeBlogCategoriesFragment = { __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null };

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