// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
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