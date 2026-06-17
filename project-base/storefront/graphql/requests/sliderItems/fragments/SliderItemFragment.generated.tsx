// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
export type TypeFriendlyUrlRouteEnum =
  | 'FRONT_ARTICLE_DETAIL'
  | 'FRONT_BLOGARTICLE_DETAIL'
  | 'FRONT_BLOGCATEGORY_DETAIL'
  | 'FRONT_BRAND_DETAIL'
  | 'FRONT_CATEGORY_SEO'
  | 'FRONT_FLAG_DETAIL'
  | 'FRONT_PRODUCT_DETAIL'
  | 'FRONT_PRODUCT_LIST'
  | 'FRONT_STORES_DETAIL';

export type TypeSliderItemFragment = { __typename: 'SliderItem', uuid: string, name: string, link: string, routeName: Types.TypeFriendlyUrlRouteEnum | null, description: string | null, rgbBackgroundColor: string, opacity: number, webMainImage: { __typename: 'Image', name: string | null, url: string }, mobileMainImage: { __typename: 'Image', name: string | null, url: string } };

export const SliderItemFragment = gql`
    fragment SliderItemFragment on SliderItem {
  __typename
  uuid
  name
  link
  routeName
  description
  rgbBackgroundColor
  opacity
  webMainImage: mainImage(type: "web") {
    ...ImageFragment
  }
  mobileMainImage: mainImage(type: "mobile") {
    ...ImageFragment
  }
}
    ${ImageFragment}`;