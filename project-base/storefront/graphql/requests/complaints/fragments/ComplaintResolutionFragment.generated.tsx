// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeComplaintResolutionFragment = (
  { __typename?: 'ComplaintResolution' }
  & Pick<Types.TypeComplaintResolution, 'name' | 'value'>
);

export const ComplaintResolutionFragment = gql`
    fragment ComplaintResolutionFragment on ComplaintResolution {
  name
  value
}
    `;