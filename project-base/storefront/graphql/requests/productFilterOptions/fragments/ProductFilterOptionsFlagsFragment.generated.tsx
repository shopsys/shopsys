// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimpleFlagFragment } from '../../flags/fragments/SimpleFlagFragment.generated';
export type TypeProductFilterOptionsFlagsFragment = (
  { __typename: 'FlagFilterOption' }
  & Pick<Types.TypeFlagFilterOption, 'count' | 'isSelected'>
  & { flag: (
    { __typename: 'Flag' }
    & Pick<Types.TypeFlag, 'uuid' | 'name' | 'rgbColor'>
  ) }
);

export const ProductFilterOptionsFlagsFragment = gql`
    fragment ProductFilterOptionsFlagsFragment on FlagFilterOption {
  __typename
  count
  flag {
    ...SimpleFlagFragment
  }
  isSelected
}
    ${SimpleFlagFragment}`;