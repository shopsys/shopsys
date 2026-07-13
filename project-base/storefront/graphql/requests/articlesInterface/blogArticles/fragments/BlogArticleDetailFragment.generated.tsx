// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../../images/fragments/ImageFragment.generated';
import { BreadcrumbFragment } from '../../../breadcrumbs/fragments/BreadcrumbFragment.generated';
import { HreflangLinksFragment } from '../../../hreflangLinks/fragments/HreflangLinksFragment.generated';
import { SimpleBlogCategoryFragment } from '../../../blogCategories/fragments/SimpleBlogCategoryFragment.generated';
import { BlogArticleAuthorFragment } from './BlogArticleAuthorFragment.generated';
export type TypeBlogArticleDetailFragment = { __typename: 'BlogArticle', id: number, uuid: string, name: string, slug: string, link: string, text: string | null, publishDate: string | null, status: string, seoTitle: string | null, seoMetaDescription: string | null, seoH1: string | null, mainBlogCategoryUuid: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, hreflangLinks: Array<{ hreflang: string, href: string }>, blogCategories: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: { name: string } | null }>, author: { __typename: 'BlogArticleAuthor', uuid: string, name: string, jobTitle: string | null, description: string | null, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | null };

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
  author {
    ...BlogArticleAuthorFragment
  }
}
    ${ImageFragment}
${BreadcrumbFragment}
${HreflangLinksFragment}
${SimpleBlogCategoryFragment}
${BlogArticleAuthorFragment}`;