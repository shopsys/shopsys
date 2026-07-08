// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
export type TypeSliderItemFragment = (
  { __typename: 'SliderItem' }
  & Pick<Types.TypeSliderItem, 'uuid' | 'name' | 'link' | 'routeName' | 'description' | 'rgbBackgroundColor' | 'opacity'>
  & { webMainImage: (
    { __typename: 'Image' }
    & Pick<Types.TypeImage, 'name' | 'url'>
  ), mobileMainImage: (
    { __typename: 'Image' }
    & Pick<Types.TypeImage, 'name' | 'url'>
  ) }
);

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