// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { PriceFragment } from '../../prices/fragments/PriceFragment.generated';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
export type TypeAdditionalServiceFragment = { __typename: 'AdditionalService', id: number, uuid: string, name: string, catnum: string, description: string | null, deliveryDaysExtension: number | null, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null };

export const AdditionalServiceFragment = gql`
    fragment AdditionalServiceFragment on AdditionalService {
  __typename
  id
  uuid
  name
  catnum
  description
  deliveryDaysExtension
  price {
    ...PriceFragment
  }
  mainImage {
    ...ImageFragment
  }
}
    ${PriceFragment}
${ImageFragment}`;