// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeImageFragment = (
  { __typename: 'Image' }
  & Pick<Types.TypeImage, 'name' | 'url'>
);

export const ImageFragment = gql`
    fragment ImageFragment on Image {
  __typename
  name
  url
}
    `;