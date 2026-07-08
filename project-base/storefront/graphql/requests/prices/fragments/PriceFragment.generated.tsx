// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypePriceFragment = (
  { __typename: 'Price' }
  & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
);

export const PriceFragment = gql`
    fragment PriceFragment on Price {
  __typename
  priceWithVat
  priceWithoutVat
  vatAmount
}
    `;