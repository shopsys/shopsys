// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
export type TypeNotificationBarsFragment = { __typename: 'NotificationBar', text: string, rgbColor: string, validityFrom: any | null, validityTo: any | null, mainImage: { __typename: 'Image', name: string | null, url: string } | null };

export const NotificationBarsFragment = gql`
    fragment NotificationBarsFragment on NotificationBar {
  __typename
  text
  rgbColor
  validityFrom
  validityTo
  mainImage {
    ...ImageFragment
  }
}
    ${ImageFragment}`;