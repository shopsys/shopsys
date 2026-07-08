// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeTokenFragments = (
  { __typename?: 'Token' }
  & Pick<Types.TypeToken, 'accessToken' | 'refreshToken'>
);

export const TokenFragments = gql`
    fragment TokenFragments on Token {
  accessToken
  refreshToken
}
    `;