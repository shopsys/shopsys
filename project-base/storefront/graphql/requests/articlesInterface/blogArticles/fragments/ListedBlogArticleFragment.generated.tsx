// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../../images/fragments/ImageFragment.generated';
import { SimpleBlogCategoryFragment } from '../../../blogCategories/fragments/SimpleBlogCategoryFragment.generated';
export type TypeListedBlogArticleFragment = { __typename: 'BlogArticle', uuid: string, name: string, link: string, publishDate: string | null, perex: string | null, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, blogCategories: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: { name: string } | null }> };

export const ListedBlogArticleFragment = gql`
    fragment ListedBlogArticleFragment on BlogArticle {
  __typename
  uuid
  name
  link
  mainImage {
    ...ImageFragment
  }
  publishDate
  perex
  slug
  blogCategories {
    ...SimpleBlogCategoryFragment
  }
}
    ${ImageFragment}
${SimpleBlogCategoryFragment}`;