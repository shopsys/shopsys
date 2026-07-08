// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimpleCategoryFragment } from '../../categories/fragments/SimpleCategoryFragment.generated';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
export type TypeAdvertsFragment_AdvertCode_ = (
  { __typename: 'AdvertCode' }
  & Pick<Types.TypeAdvertCode, 'code' | 'uuid' | 'name' | 'positionName' | 'type'>
  & { categories: Array<(
    { __typename: 'Category' }
    & Pick<Types.TypeCategory, 'uuid' | 'name' | 'slug'>
  )> }
);

export type TypeAdvertsFragment_AdvertImage_ = (
  { __typename: 'AdvertImage' }
  & Pick<Types.TypeAdvertImage, 'link' | 'uuid' | 'name' | 'positionName' | 'type'>
  & { mainImage: Types.Maybe<(
    { __typename: 'Image' }
    & Pick<Types.TypeImage, 'name' | 'url'>
  )>, mainImageMobile: Types.Maybe<(
    { __typename: 'Image' }
    & Pick<Types.TypeImage, 'name' | 'url'>
  )>, categories: Array<(
    { __typename: 'Category' }
    & Pick<Types.TypeCategory, 'uuid' | 'name' | 'slug'>
  )> }
);

export type TypeAdvertsFragment = TypeAdvertsFragment_AdvertCode_ | TypeAdvertsFragment_AdvertImage_;

export const AdvertsFragment = gql`
    fragment AdvertsFragment on Advert {
  __typename
  uuid
  name
  positionName
  type
  categories {
    ...SimpleCategoryFragment
  }
  ... on AdvertCode {
    code
  }
  ... on AdvertImage {
    link
    mainImage(type: "web") {
      ...ImageFragment
    }
    mainImageMobile: mainImage(type: "mobile") {
      ...ImageFragment
    }
  }
}
    ${SimpleCategoryFragment}
${ImageFragment}`;