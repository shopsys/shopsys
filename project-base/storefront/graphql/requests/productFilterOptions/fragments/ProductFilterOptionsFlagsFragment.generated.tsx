// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimpleFlagFragment } from '../../flags/fragments/SimpleFlagFragment.generated';
export type TypeProductFilterOptionsFlagsFragment = { __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } };

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