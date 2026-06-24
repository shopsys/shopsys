// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypePriceFragment = { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string };

export const PriceFragment = gql`
    fragment PriceFragment on Price {
  __typename
  priceWithVat
  priceWithoutVat
  vatAmount
}
    `;