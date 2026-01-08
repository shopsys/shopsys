// @ts-nocheck
import * as Types from '../../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../../images/fragments/ImageFragment.generated';
import { SimpleBlogCategoryFragment } from '../../../blogCategories/fragments/SimpleBlogCategoryFragment.generated';
export type TypeListedBlogArticleFragment = { __typename: 'BlogArticle', uuid: string, name: string, link: string, publishDate: any, perex: string | null, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, blogCategories: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: { __typename?: 'BlogCategory', name: string } | null }> };

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