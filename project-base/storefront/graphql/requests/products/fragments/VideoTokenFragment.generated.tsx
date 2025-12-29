// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeVideoTokenFragment = { __typename: 'VideoToken', description: string | null, token: string };

export const VideoTokenFragment = gql`
    fragment VideoTokenFragment on VideoToken {
  __typename
  description
  token
}
    `;