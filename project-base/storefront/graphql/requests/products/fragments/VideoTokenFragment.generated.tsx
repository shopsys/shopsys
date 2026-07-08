// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeVideoTokenFragment = (
  { __typename: 'VideoToken' }
  & Pick<Types.TypeVideoToken, 'description' | 'token'>
);

export const VideoTokenFragment = gql`
    fragment VideoTokenFragment on VideoToken {
  __typename
  description
  token
}
    `;