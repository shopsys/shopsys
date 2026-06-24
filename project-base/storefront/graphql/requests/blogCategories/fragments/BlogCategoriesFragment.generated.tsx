// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimpleBlogCategoryFragment } from './SimpleBlogCategoryFragment.generated';
export type TypeBlogCategoriesFragment = { __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: { name: string } | null }>, parent: { name: string } | null }>, parent: { name: string } | null }>, parent: { name: string } | null }>, parent: { name: string } | null };

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