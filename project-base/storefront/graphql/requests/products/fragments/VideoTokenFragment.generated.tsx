// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
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