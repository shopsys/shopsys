// @ts-nocheck
import * as Types from '../../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../../images/fragments/ImageFragment.generated';
import { SimpleBlogCategoryFragment } from '../../../blogCategories/fragments/SimpleBlogCategoryFragment.generated';
export type TypeListedBlogArticleFragment = (
  { __typename: 'BlogArticle' }
  & Pick<Types.TypeBlogArticle, 'uuid' | 'name' | 'link' | 'publishDate' | 'perex' | 'slug'>
  & { mainImage: Types.Maybe<(
    { __typename: 'Image' }
    & Pick<Types.TypeImage, 'name' | 'url'>
  )>, blogCategories: Array<(
    { __typename: 'BlogCategory' }
    & Pick<Types.TypeBlogCategory, 'uuid' | 'name' | 'link'>
    & { parent: Types.Maybe<(
      { __typename?: 'BlogCategory' }
      & Pick<Types.TypeBlogCategory, 'name'>
    )> }
  )> }
);

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