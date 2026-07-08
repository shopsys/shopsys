// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
export type TypeNotificationBarsFragment = (
  { __typename: 'NotificationBar' }
  & Pick<Types.TypeNotificationBar, 'uuid' | 'text' | 'rgbColor' | 'validityFrom' | 'validityTo'>
  & { mainImage: Types.Maybe<(
    { __typename: 'Image' }
    & Pick<Types.TypeImage, 'name' | 'url'>
  )> }
);

export const NotificationBarsFragment = gql`
    fragment NotificationBarsFragment on NotificationBar {
  __typename
  uuid
  text
  rgbColor
  validityFrom
  validityTo
  mainImage {
    ...ImageFragment
  }
}
    ${ImageFragment}`;