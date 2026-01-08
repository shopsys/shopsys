// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeAvailabilityFragment = { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum };

export const AvailabilityFragment = gql`
    fragment AvailabilityFragment on Availability {
  __typename
  name
  status
}
    `;