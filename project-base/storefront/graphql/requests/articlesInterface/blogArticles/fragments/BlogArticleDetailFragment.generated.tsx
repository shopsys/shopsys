// @ts-nocheck
import * as Types from '../../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../../images/fragments/ImageFragment.generated';
import { BreadcrumbFragment } from '../../../breadcrumbs/fragments/BreadcrumbFragment.generated';
import { HreflangLinksFragment } from '../../../hreflangLinks/fragments/HreflangLinksFragment.generated';
import { SimpleBlogCategoryFragment } from '../../../blogCategories/fragments/SimpleBlogCategoryFragment.generated';
export type TypeBlogArticleDetailFragment = (
  { __typename: 'BlogArticle' }
  & Pick<Types.TypeBlogArticle, 'id' | 'uuid' | 'name' | 'slug' | 'link' | 'text' | 'publishDate' | 'status' | 'seoTitle' | 'seoMetaDescription' | 'seoH1' | 'mainBlogCategoryUuid'>
  & { mainImage: Types.Maybe<(
    { __typename: 'Image' }
    & Pick<Types.TypeImage, 'name' | 'url'>
  )>, breadcrumb: Array<(
    { __typename: 'Link' }
    & Pick<Types.TypeLink, 'name' | 'slug'>
  )>, hreflangLinks: Array<(
    { __typename?: 'HreflangLink' }
    & Pick<Types.TypeHreflangLink, 'hreflang' | 'href'>
  )>, blogCategories: Array<(
    { __typename: 'BlogCategory' }
    & Pick<Types.TypeBlogCategory, 'uuid' | 'name' | 'link'>
    & { parent: Types.Maybe<(
      { __typename?: 'BlogCategory' }
      & Pick<Types.TypeBlogCategory, 'name'>
    )> }
  )> }
);

export const BlogArticleDetailFragment = gql`
    fragment BlogArticleDetailFragment on BlogArticle {
  __typename
  id
  uuid
  name
  slug
  link
  mainImage {
    ...ImageFragment
  }
  breadcrumb {
    ...BreadcrumbFragment
  }
  text
  publishDate
  status
  seoTitle
  seoMetaDescription
  seoH1
  hreflangLinks {
    ...HreflangLinksFragment
  }
  mainBlogCategoryUuid
  blogCategories {
    ...SimpleBlogCategoryFragment
  }
}
    ${ImageFragment}
${BreadcrumbFragment}
${HreflangLinksFragment}
${SimpleBlogCategoryFragment}`;