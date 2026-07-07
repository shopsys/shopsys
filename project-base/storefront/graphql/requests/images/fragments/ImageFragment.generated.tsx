// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
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