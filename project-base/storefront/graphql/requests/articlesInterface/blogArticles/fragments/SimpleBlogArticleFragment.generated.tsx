// @ts-nocheck
import * as Types from '../../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../../images/fragments/ImageFragment.generated';
export type TypeSimpleBlogArticleFragment = { __typename: 'BlogArticle', name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null };

export const SimpleBlogArticleFragment = gql`
    fragment SimpleBlogArticleFragment on BlogArticle {
  __typename
  name
  slug
  mainImage {
    ...ImageFragment
  }
}
    ${ImageFragment}`;