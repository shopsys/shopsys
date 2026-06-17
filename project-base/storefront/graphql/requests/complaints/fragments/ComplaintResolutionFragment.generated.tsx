// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeComplaintResolutionFragment = { name: string, value: string };

export const ComplaintResolutionFragment = gql`
    fragment ComplaintResolutionFragment on ComplaintResolution {
  name
  value
}
    `;