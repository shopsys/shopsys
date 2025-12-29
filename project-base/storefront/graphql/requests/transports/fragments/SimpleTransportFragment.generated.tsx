// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeSimpleTransportFragment = { __typename: 'Transport', uuid: string, name: string, description: string | null, transportTypeCode: Types.TypeTransportTypeEnum };

export const SimpleTransportFragment = gql`
    fragment SimpleTransportFragment on Transport {
  __typename
  uuid
  name
  description
  transportTypeCode
}
    `;