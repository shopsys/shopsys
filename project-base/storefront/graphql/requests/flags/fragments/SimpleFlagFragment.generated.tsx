// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeSimpleFlagFragment = (
  { __typename: 'Flag' }
  & Pick<Types.TypeFlag, 'uuid' | 'name' | 'rgbColor'>
);

export const SimpleFlagFragment = gql`
    fragment SimpleFlagFragment on Flag {
  __typename
  uuid
  name
  rgbColor
}
    `;