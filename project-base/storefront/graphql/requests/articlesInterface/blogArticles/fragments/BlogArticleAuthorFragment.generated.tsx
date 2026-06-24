// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../../images/fragments/ImageFragment.generated';
export type TypeBlogArticleAuthorFragment = { __typename: 'BlogArticleAuthor', uuid: string, name: string, jobTitle: string | null, description: string | null, mainImage: { __typename: 'Image', name: string | null, url: string } | null };

export const BlogArticleAuthorFragment = gql`
    fragment BlogArticleAuthorFragment on BlogArticleAuthor {
  __typename
  uuid
  name
  jobTitle
  description
  mainImage {
    ...ImageFragment
  }
}
    ${ImageFragment}`;