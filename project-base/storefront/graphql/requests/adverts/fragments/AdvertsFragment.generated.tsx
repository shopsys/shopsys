// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimpleCategoryFragment } from '../../categories/fragments/SimpleCategoryFragment.generated';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
export type TypeAdvertsFragment_AdvertCode_ = { __typename: 'AdvertCode', code: string, uuid: string, name: string, positionName: string, type: string, categories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string }> };

export type TypeAdvertsFragment_AdvertImage_ = { __typename: 'AdvertImage', link: string | null, uuid: string, name: string, positionName: string, type: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, mainImageMobile: { __typename: 'Image', name: string | null, url: string } | null, categories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string }> };

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