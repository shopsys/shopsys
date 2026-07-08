// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { DeliveryAddressFragment } from '../fragments/DeliveryAddressFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeEditDeliveryAddressMutationVariables = Types.Exact<{
  input: Types.TypeDeliveryAddressInput;
}>;


export type TypeEditDeliveryAddressMutation = (
  { __typename?: 'Mutation' }
  & { EditDeliveryAddress: Array<(
    { __typename: 'DeliveryAddress' }
    & Pick<Types.TypeDeliveryAddress, 'uuid' | 'companyName' | 'street' | 'city' | 'postcode' | 'telephone' | 'firstName' | 'lastName'>
    & { telephoneData: Types.Maybe<(
      { __typename?: 'PhoneData' }
      & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
    )>, country: Types.Maybe<(
      { __typename: 'Country' }
      & Pick<Types.TypeCountry, 'name' | 'code'>
    )> }
  )> }
);


export const EditDeliveryAddressMutationDocument = gql`
    mutation EditDeliveryAddressMutation($input: DeliveryAddressInput!) {
  EditDeliveryAddress(input: $input) {
    ...DeliveryAddressFragment
  }
}
    ${DeliveryAddressFragment}`;

export function useEditDeliveryAddressMutation() {
  return Urql.useMutation<TypeEditDeliveryAddressMutation, TypeEditDeliveryAddressMutationVariables>(EditDeliveryAddressMutationDocument);
};