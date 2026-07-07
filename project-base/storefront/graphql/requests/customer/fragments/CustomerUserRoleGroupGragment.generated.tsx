// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeCustomerUserRoleGroupFragment = { __typename: 'CustomerUserRoleGroup', uuid: string, name: string };

export const CustomerUserRoleGroupFragment = gql`
    fragment CustomerUserRoleGroupFragment on CustomerUserRoleGroup {
  __typename
  uuid
  name
}
    `;