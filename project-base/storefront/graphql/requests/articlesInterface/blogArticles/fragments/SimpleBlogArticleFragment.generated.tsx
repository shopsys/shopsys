// @ts-nocheck
import * as Types from '../../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../../images/fragments/ImageFragment.generated';
export type TypeSimpleBlogArticleFragment = (
  { __typename: 'BlogArticle' }
  & Pick<Types.TypeBlogArticle, 'name' | 'slug'>
  & { mainImage: Types.Maybe<(
    { __typename: 'Image' }
    & Pick<Types.TypeImage, 'name' | 'url'>
  )> }
);

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