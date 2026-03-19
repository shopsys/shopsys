// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimpleCustomerUserFragment } from '../fragments/SimpleCustomerUserFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeEditCustomerUserPersonalDataMutationVariables = Types.Exact<{
  input: Types.TypeEditCustomerUserPersonalDataInput;
}>;


export type TypeEditCustomerUserPersonalDataMutation = { __typename?: 'Mutation', EditCustomerUserPersonalData: { __typename: 'CompanyCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, roles: Array<Types.TypeCustomerUserRoleEnum>, telephoneData: { __typename?: 'PhoneData', prefix: string | null, countryCode: string | null, number: string } | null, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string } } | { __typename: 'CurrentCompanyCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, roles: Array<Types.TypeCustomerUserRoleEnum>, telephoneData: { __typename?: 'PhoneData', prefix: string | null, countryCode: string | null, number: string } | null, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string } } | { __typename: 'CurrentRegularCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, roles: Array<Types.TypeCustomerUserRoleEnum>, telephoneData: { __typename?: 'PhoneData', prefix: string | null, countryCode: string | null, number: string } | null, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string } } | { __typename: 'RegularCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, roles: Array<Types.TypeCustomerUserRoleEnum>, telephoneData: { __typename?: 'PhoneData', prefix: string | null, countryCode: string | null, number: string } | null, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string } } };


export const EditCustomerUserPersonalDataMutationDocument = gql`
    mutation EditCustomerUserPersonalDataMutation($input: EditCustomerUserPersonalDataInput!) {
  EditCustomerUserPersonalData(input: $input) {
    ...SimpleCustomerUserFragment
  }
}
    ${SimpleCustomerUserFragment}`;

export function useEditCustomerUserPersonalDataMutation() {
  return Urql.useMutation<TypeEditCustomerUserPersonalDataMutation, TypeEditCustomerUserPersonalDataMutationVariables>(EditCustomerUserPersonalDataMutationDocument);
};