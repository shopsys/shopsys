// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeComplaintResolutionFragment = { __typename?: 'ComplaintResolution', name: string, value: string };

export const ComplaintResolutionFragment = gql`
    fragment ComplaintResolutionFragment on ComplaintResolution {
  name
  value
}
    `;