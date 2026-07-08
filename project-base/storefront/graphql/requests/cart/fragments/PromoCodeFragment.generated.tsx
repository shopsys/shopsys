// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypePromoCodeFragment = (
  { __typename: 'PromoCode' }
  & Pick<Types.TypePromoCode, 'code' | 'type'>
  & { discountPrice: (
    { __typename?: 'Price' }
    & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode'>
  ) }
);

export const PromoCodeFragment = gql`
    fragment PromoCodeFragment on PromoCode {
  __typename
  code
  type
  discountPrice {
    priceWithVat
    priceWithoutVat
    vatAmount
    currencyCode
  }
}
    `;