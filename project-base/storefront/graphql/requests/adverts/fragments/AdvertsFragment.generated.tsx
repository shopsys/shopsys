// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
export type TypeAdvertsFragment_AdvertCode = { __typename: 'AdvertCode', code: string, uuid: string, name: string, positionName: string };

export type TypeAdvertsFragment_AdvertImage = { __typename: 'AdvertImage', link: string | null, uuid: string, name: string, positionName: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, mainImageMobile: { __typename: 'Image', name: string | null, url: string } | null };

export type TypeAdvertsFragment =
  | TypeAdvertsFragment_AdvertCode
  | TypeAdvertsFragment_AdvertImage
;

export const AdvertsFragment = gql`
    fragment AdvertsFragment on Advert {
  __typename
  uuid
  name
  positionName
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
    ${ImageFragment}`;