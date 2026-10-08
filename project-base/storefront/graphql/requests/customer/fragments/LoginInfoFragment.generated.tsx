// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
/** One of the possible methods of the customer user login */
export type TypeLoginTypeEnum =
  | 'admin'
  | 'facebook'
  | 'google'
  | 'seznam'
  | 'web';

export type TypeLoginInfoFragment = { __typename: 'LoginInfo', externalId: string | null, loginType: Types.TypeLoginTypeEnum };

export const LoginInfoFragment = gql`
    fragment LoginInfoFragment on LoginInfo {
  __typename
  externalId
  loginType
}
    `;