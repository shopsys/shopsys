// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeCustomerUserRoleGroupFragment = (
  { __typename: 'CustomerUserRoleGroup' }
  & Pick<Types.TypeCustomerUserRoleGroup, 'uuid' | 'name'>
);

export const CustomerUserRoleGroupFragment = gql`
    fragment CustomerUserRoleGroupFragment on CustomerUserRoleGroup {
  __typename
  uuid
  name
}
    `;