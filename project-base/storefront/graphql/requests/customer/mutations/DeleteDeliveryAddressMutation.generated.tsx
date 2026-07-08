// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { DeliveryAddressFragment } from '../fragments/DeliveryAddressFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeDeleteDeliveryAddressMutationVariables = Types.Exact<{
  deliveryAddressUuid: Types.Scalars['Uuid']['input'];
}>;


export type TypeDeleteDeliveryAddressMutation = (
  { __typename?: 'Mutation' }
  & { DeleteDeliveryAddress: Array<(
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


export const DeleteDeliveryAddressMutationDocument = gql`
    mutation DeleteDeliveryAddressMutation($deliveryAddressUuid: Uuid!) {
  DeleteDeliveryAddress(deliveryAddressUuid: $deliveryAddressUuid) {
    ...DeliveryAddressFragment
  }
}
    ${DeliveryAddressFragment}`;

export function useDeleteDeliveryAddressMutation() {
  return Urql.useMutation<TypeDeleteDeliveryAddressMutation, TypeDeleteDeliveryAddressMutationVariables>(DeleteDeliveryAddressMutationDocument);
};