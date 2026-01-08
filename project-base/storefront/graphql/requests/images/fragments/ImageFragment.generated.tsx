// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeImageFragment = { __typename: 'Image', name: string | null, url: string };

export const ImageFragment = gql`
    fragment ImageFragment on Image {
  __typename
  name
  url
}
    `;