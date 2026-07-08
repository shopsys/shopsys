// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeLoginInfoFragment = (
  { __typename: 'LoginInfo' }
  & Pick<Types.TypeLoginInfo, 'externalId' | 'loginType'>
);

export const LoginInfoFragment = gql`
    fragment LoginInfoFragment on LoginInfo {
  __typename
  externalId
  loginType
}
    `;