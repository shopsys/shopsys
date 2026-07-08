// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypePageInfoFragment = (
  { __typename: 'PageInfo' }
  & Pick<Types.TypePageInfo, 'hasNextPage' | 'hasPreviousPage' | 'endCursor'>
);

export const PageInfoFragment = gql`
    fragment PageInfoFragment on PageInfo {
  __typename
  hasNextPage
  hasPreviousPage
  endCursor
}
    `;